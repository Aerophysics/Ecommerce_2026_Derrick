<?php

// Pull in the connection settings (DATABASE, SERVER, USERNAME, PASSWD constants).
// __DIR__ makes this work no matter which file/folder does the including,
// because it is always resolved relative to THIS file, not the entry script.
require_once __DIR__ . "/db_cred.php";

// Database is the base class every "model" class (like Customer) should extend.
// It knows how to connect to MySQL and how to run queries safely (using
// prepared statements, which protect against SQL injection). Child classes
// don't need to know any of this - they just call $this->fetchAll(), etc.
class Database
{
    // $conn holds the PDO connection object. It is private because only
    // this class needs to touch it directly - child classes use the
    // helper methods below instead.
    private $conn;

    // These properties are set from the constants defined in db_cred.php.
    // Using properties (instead of the constants directly) makes it easy
    // to override them later if a subclass ever needs a different database.
    private $host = SERVER;
    private $dbname = DATABASE;
    private $username = USERNAME;
    private $password = PASSWD;

    // The constructor runs automatically whenever `new Database()` (or
    // `new Customer()`, since Customer extends Database) is called.
    // It opens the connection so every child class is ready to query
    // the database as soon as it is created.
    public function __construct()
    {
        try {

            // PDO is PHP's built-in database access layer. The first
            // argument is the DSN (Data Source Name) - it tells PDO
            // which driver, host and database to use.
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password
            );

            // Show database errors as exceptions
            // (instead of PHP warnings, which are easy to miss)
            $this->conn->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );

        } catch (PDOException $e) {

            // If the connection fails (wrong credentials, MySQL not
            // running, etc.) stop the script and show why.
            die("Database connection failed: " . $e->getMessage());

        }
    }

    // Execute SELECT queries that can return many rows.
    // $sql is the query text with "?" placeholders.
    // $params is an array of values to safely fill those placeholders.
    // Example: $this->fetchAll("SELECT * FROM customer WHERE customer_city = ?", ["Lagos"]);
    public function fetchAll($sql, $params = [])
    {
        // Prepare the query (PDO checks its structure before running it)
        $stmt = $this->conn->prepare($sql);

        // Safely bind parameters with appropriate data types (e.g. PARAM_INT for LIMIT)
        foreach ($params as $key => $val) {
            $paramIndex = is_int($key) ? $key + 1 : $key;
            $type = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($paramIndex, $val, $type);
        }

        // Run the query
        $stmt->execute();

        // Return every matching row as an associative array (or empty array if none)
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return is_array($rows) ? $rows : [];
    }

    // Execute a SELECT query that is only expected to return one row
    // (e.g. looking up a single customer by id).
    public function fetchOne($sql, $params = [])
    {
        $stmt = $this->conn->prepare($sql);

        foreach ($params as $key => $val) {
            $paramIndex = is_int($key) ? $key + 1 : $key;
            $type = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($paramIndex, $val, $type);
        }

        $stmt->execute();

        // fetch() (no "All") returns just the first matching row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: false;
    }

    // Execute INSERT, UPDATE and DELETE statements.
    // Returns true/false depending on whether the statement succeeded.
    public function execute($sql, $params = [])
    {
        $stmt = $this->conn->prepare($sql);

        foreach ($params as $key => $val) {
            $paramIndex = is_int($key) ? $key + 1 : $key;
            $type = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($paramIndex, $val, $type);
        }

        return $stmt->execute();
    }

    // Get the raw PDO connection if a child class ever needs to do
    // something this class doesn't already provide a helper for
    // (e.g. transactions).
    public function getConnection()
    {
        return $this->conn;
    }
}
