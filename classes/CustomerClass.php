<?php

// Bring in the Database base class. __DIR__ keeps this include working
// no matter which controller/action pulls this file in.
require_once __DIR__ . "/../core/db_class.php";

// CustomerClass is the Model for the `customer` table. It contains ONLY
// SQL and data logic - no HTML, no echo, no $_POST, no redirects. It
// extends Database, inheriting the prepared-statement helpers
// fetchAll() / fetchOne() / execute().
class CustomerClass extends Database
{
    // Return true if a customer already exists with this email, false
    // otherwise. Used to block duplicate registrations. Prepared statement
    // via the "?" placeholder - never string concatenation.
    public function emailExists($email)
    {
        $sql = "SELECT customer_email FROM customer WHERE customer_email = ?";
        $row = $this->fetchOne($sql, [$email]);
        return $row !== false && $row !== null;
    }

    // Insert a new customer. The password is hashed HERE with bcrypt so a
    // caller can never accidentally store it in plain text. user_role is
    // not a parameter - every sign-up is a regular customer (DEFAULT 2 in
    // the schema). Returns true on success, false on failure.
    public function addCustomer($name, $email, $pass, $country, $city, $contact)
    {
        $hash = password_hash($pass, PASSWORD_BCRYPT);

        $sql = "INSERT INTO customer
                    (customer_name, customer_email, customer_pass,
                     customer_country, customer_city, customer_contact)
                VALUES (?, ?, ?, ?, ?, ?)";

        return $this->execute($sql, [$name, $email, $hash, $country, $city, $contact]);
    }

    // Fetch a single customer row by email, or false if none exists.
    // Returns the full row (including the password hash) because login()
    // needs the hash to verify against.
    public function getCustomerByEmail($email)
    {
        $sql = "SELECT * FROM customer WHERE customer_email = ?";
        $row = $this->fetchOne($sql, [$email]);
        return $row ?: false;
    }

    // Verify a login attempt. Looks the customer up by email, then checks
    // the supplied password against the stored bcrypt hash with
    // password_verify(). Returns the customer row on success, false on
    // any failure (unknown email or wrong password).
    public function login($email, $pass)
    {
        $row = $this->getCustomerByEmail($email);
        if ($row && password_verify($pass, $row['customer_pass'])) {
            return $row;
        }
        return false;
    }
}
