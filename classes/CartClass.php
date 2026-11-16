<?php
/**
 * CartClass.php — the Model for the `cart` table (Tasks 11 & 12).
 *
 * MVC note: SQL and data logic only. No HTML, no echo, no $_POST/$_SESSION.
 * Every query that touches user input uses a prepared statement.
 *
 * Carts are keyed by IP address, not by customer id, so a guest can shop
 * without an account. Because the IP does not change when someone logs in
 * mid-session, the cart they built as a guest simply carries over.
 */

require_once __DIR__ . '/../core/db_class.php';

class Cart extends Database
{
    /**
     * Is this product already in this visitor's cart? (Task 11)
     * The handout asks for the duplicate check here rather than relying on
     * the UNIQUE (p_id, ip_add) key, so the user gets a readable message
     * instead of a constraint error.
     * @return bool
     */
    public function isInCart($productId, $ip)
    {
        $row = $this->fetchOne(
            'SELECT p_id FROM cart WHERE p_id = ? AND ip_add = ?',
            [$productId, $ip]
        );
        return $row !== false;
    }

    /**
     * Put a product in the cart (Task 11). $customerId is recorded when the
     * visitor happens to be logged in; it stays NULL for guests.
     * @return bool true on success
     */
    public function addToCart($productId, $qty, $ip, $customerId = null)
    {
        if ($customerId !== null) {
            $exists = $this->fetchOne(
                'SELECT customer_id FROM customer WHERE customer_id = ?',
                [(int) $customerId]
            );
            if (!$exists) {
                $customerId = null;
            }
        }

        return $this->execute(
            'INSERT INTO cart (p_id, ip_add, c_id, qty) VALUES (?, ?, ?, ?)',
            [$productId, $ip, $customerId, $qty]
        );
    }

    /**
     * Cart contents joined to the products, so the view has titles, prices
     * and images without running a second query per row (Task 11).
     * @return array list of rows (empty array if the cart is empty)
     */
    public function getCartItems($ip)
    {
        return $this->fetchAll(
            'SELECT c.p_id, c.qty,
                    p.product_title, p.product_price, p.product_image
               FROM cart c
               JOIN products p ON p.product_id = c.p_id
              WHERE c.ip_add = ?
              ORDER BY c.p_id ASC',
            [$ip]
        );
    }

    /**
     * How many distinct products are in the cart (Task 11, header summary).
     * @return int
     */
    public function getCartCount($ip)
    {
        $row = $this->fetchOne('SELECT COUNT(*) AS c FROM cart WHERE ip_add = ?', [$ip]);
        return (int) ($row['c'] ?? 0);
    }

    /**
     * Cart value (Task 11, header summary).
     *
     * Summed in SQL rather than by looping rows in PHP: one round trip, and
     * the multiplication happens in DECIMAL arithmetic, so prices do not
     * pick up the rounding error that floats would introduce.
     * @return float
     */
    public function getCartTotal($ip)
    {
        $row = $this->fetchOne(
            'SELECT COALESCE(SUM(c.qty * p.product_price), 0) AS total
               FROM cart c
               JOIN products p ON p.product_id = c.p_id
              WHERE c.ip_add = ?',
            [$ip]
        );
        return (float) ($row['total'] ?? 0);
    }

    /**
     * Drop one product from this visitor's cart (Task 12).
     * The ip_add in the WHERE clause is the authorisation check: it stops
     * one visitor deleting a row from somebody else's cart.
     * @return bool true on success
     */
    public function removeFromCart($productId, $ip)
    {
        return $this->execute(
            'DELETE FROM cart WHERE p_id = ? AND ip_add = ?',
            [$productId, $ip]
        );
    }

    /**
     * Change a line's quantity (Task 12). Quantity must be at least 1 —
     * removing an item is removeFromCart()'s job, not a qty of 0.
     * @return bool true if the update ran
     */
    public function updateQty($productId, $newQty, $ip)
    {
        $newQty = (int) $newQty;
        if ($newQty < 1) {
            return false;
        }
        return $this->execute(
            'UPDATE cart SET qty = ? WHERE p_id = ? AND ip_add = ?',
            [$newQty, $productId, $ip]
        );
    }

    /** Empty this visitor's cart — used after a successful payment (Task 13). */
    public function clearCart($ip)
    {
        return $this->execute('DELETE FROM cart WHERE ip_add = ?', [$ip]);
    }

    // ── Payment & Orders (Task 13) ──────────────────────────

    /**
     * Check whether a specific column exists on a table in the active database.
     * Supports both the original handout schema and the extended schema.
     *
     * @param string $table
     * @param string $column
     * @return bool
     */
    private function hasColumn($table, $column)
    {
        try {
            $row = $this->fetchOne(
                'SELECT 1 FROM information_schema.COLUMNS
                  WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = ?
                    AND COLUMN_NAME = ?
                  LIMIT 1',
                [$table, $column]
            );
            return $row !== false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Process a verified payment inside a single database transaction.
     *
     * Steps executed atomically:
     *   1. Generates a unique 6-digit invoice number (mt_rand(100000, 999999)).
     *   2. Inserts into `orders` table (customer_id, invoice_no, order_date, order_status='paid').
     *   3. Copies each item in the customer's cart into `orderdetails` (order_id, product_id, qty).
     *   4. Records the transaction in `payment` (amt, customer_id, order_id, currency, payment_date).
     *   5. Clears the cart rows for this visitor IP.
     *   6. Returns the newly created order_id.
     *
     * If any query fails, the transaction is rolled back and no partial records remain.
     *
     * @param int    $customerId Customer's database ID
     * @param string $ip         Client IP address the cart is keyed by
     * @param string $reference  Paystack transaction reference
     * @param float  $amount     Total verified amount in GHS
     * @return int|false         New order_id on success, false on failure
     */
    public function processPayment($customerId, $ip, $reference, $amount)
    {
        $customerId = (int) $customerId;
        if ($customerId <= 0) {
            return false;
        }

        $hasOrdersCreatedAt = $this->hasColumn('orders', 'created_at');
        $hasPaymentRef      = $this->hasColumn('payment', 'reference');

        try {
            $this->conn->beginTransaction();

            // Fetch current cart items to copy into order details
            $cartItems = $this->fetchAll(
                'SELECT p_id, qty FROM cart WHERE ip_add = ?',
                [$ip]
            );

            if (empty($cartItems)) {
                $this->conn->rollBack();
                return false;
            }

            // 1. Generate unique 6-digit invoice number
            do {
                $invoiceNo = mt_rand(100000, 999999);
                $exists = $this->fetchOne('SELECT order_id FROM orders WHERE invoice_no = ?', [$invoiceNo]);
            } while ($exists !== false);

            $orderDate = date('Y-m-d');
            $now       = date('Y-m-d H:i:s');

            // 2. INSERT into orders table
            if ($hasOrdersCreatedAt) {
                $stmtOrder = $this->conn->prepare(
                    'INSERT INTO orders (customer_id, invoice_no, order_date, order_status, created_at)
                     VALUES (?, ?, ?, ?, ?)'
                );
                $stmtOrder->execute([$customerId, $invoiceNo, $orderDate, 'paid', $now]);
            } else {
                $stmtOrder = $this->conn->prepare(
                    'INSERT INTO orders (customer_id, invoice_no, order_date, order_status)
                     VALUES (?, ?, ?, ?)'
                );
                $stmtOrder->execute([$customerId, $invoiceNo, $orderDate, 'paid']);
            }
            $orderId = (int) $this->conn->lastInsertId();

            if ($orderId <= 0) {
                $this->conn->rollBack();
                return false;
            }

            // 3. Copy cart rows into orderdetails table
            $stmtDetail = $this->conn->prepare(
                'INSERT INTO orderdetails (order_id, product_id, qty) VALUES (?, ?, ?)'
            );
            foreach ($cartItems as $item) {
                $stmtDetail->execute([$orderId, (int) $item['p_id'], (int) $item['qty']]);
            }

            // 4. Record payment in payment table
            if ($hasPaymentRef) {
                $stmtPayment = $this->conn->prepare(
                    'INSERT INTO payment (amt, customer_id, order_id, currency, reference, payment_date)
                     VALUES (?, ?, ?, ?, ?, ?)'
                );
                $stmtPayment->execute([
                    (float) $amount,
                    $customerId,
                    $orderId,
                    'GHS',
                    $reference,
                    $orderDate
                ]);
            } else {
                $stmtPayment = $this->conn->prepare(
                    'INSERT INTO payment (amt, customer_id, order_id, currency, payment_date)
                     VALUES (?, ?, ?, ?, ?)'
                );
                $stmtPayment->execute([
                    (float) $amount,
                    $customerId,
                    $orderId,
                    'GHS',
                    $orderDate
                ]);
            }

            // 5. DELETE from cart WHERE ip_add = ?
            $stmtDelete = $this->conn->prepare('DELETE FROM cart WHERE ip_add = ?');
            $stmtDelete->execute([$ip]);

            // Commit transaction
            $this->conn->commit();
            return $orderId;

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            if (function_exists('log_error')) {
                log_error('processPayment failed: ' . $e->getMessage());
            } else {
                error_log('processPayment failed: ' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Fetch complete order details joined with orderdetails, customer info,
     * payment record, and product titles/prices (Task 13).
     *
     * @param int $orderId Order primary key
     * @return array|false Full order data array or false if not found
     */
    public function getOrderById($orderId)
    {
        $orderId = (int) $orderId;
        if ($orderId <= 0) {
            return false;
        }

        // Fetch order header with customer and payment info (SELECT o.*, c.*, p.* avoids missing optional columns)
        $order = $this->fetchOne(
            'SELECT o.*,
                    c.customer_name, c.customer_email, c.customer_contact, c.customer_city,
                    c.customer_country,
                    p.pay_id, p.amt AS payment_amt, p.currency AS payment_currency,
                    p.payment_date
               FROM orders o
               JOIN customer c ON c.customer_id = o.customer_id
               LEFT JOIN payment p ON p.order_id = o.order_id
              WHERE o.order_id = ?',
            [$orderId]
        );

        if (!$order) {
            return false;
        }

        if ($this->hasColumn('customer', 'customer_address')) {
            $addrRow = $this->fetchOne('SELECT customer_address FROM customer WHERE customer_id = ?', [$order['customer_id']]);
            $order['customer_address'] = $addrRow['customer_address'] ?? '';
        } else {
            $order['customer_address'] = '';
        }

        if ($this->hasColumn('payment', 'reference')) {
            $refRow = $this->fetchOne('SELECT reference FROM payment WHERE order_id = ?', [$orderId]);
            $order['payment_reference'] = $refRow['reference'] ?? null;
        } else {
            $order['payment_reference'] = null;
        }

        // Fetch item rows joined with products
        $items = $this->fetchAll(
            'SELECT od.product_id, od.qty,
                    pr.product_title, pr.product_price, pr.product_image,
                    (od.qty * pr.product_price) AS line_total
               FROM orderdetails od
               JOIN products pr ON pr.product_id = od.product_id
              WHERE od.order_id = ?
              ORDER BY pr.product_title ASC',
            [$orderId]
        );

        $order['items'] = is_array($items) ? $items : [];

        // Compute total amount
        $calcTotal = 0;
        foreach ($order['items'] as $item) {
            $calcTotal += (float) $item['line_total'];
        }
        $order['total'] = (float) ($order['payment_amt'] ?? $calcTotal);

        return $order;
    }

    /**
     * Retrieve all orders for a specific customer, newest first.
     *
     * @param int $customerId
     * @return array List of customer orders
     */
    public function getCustomerOrders($customerId)
    {
        $refSelect = $this->hasColumn('payment', 'reference') ? ', p.reference' : '';
        return $this->fetchAll(
            'SELECT o.*, p.amt' . $refSelect . '
               FROM orders o
               LEFT JOIN payment p ON p.order_id = o.order_id
              WHERE o.customer_id = ?
              ORDER BY o.order_id DESC',
            [(int) $customerId]
        );
    }
}

// Support CartClass alias if referenced by that name
if (!class_exists('CartClass')) {
    class_alias('Cart', 'CartClass');
}

