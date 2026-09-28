<?php

// Bring in the Customer model. __DIR__ keeps the include stable.
require_once __DIR__ . "/../classes/CustomerClass.php";

// CustomerController is the traffic director between the action files and
// the CustomerClass model. It instantiates the model, calls its methods,
// and returns structured result arrays. It contains NO SQL, NO HTML,
// NO echo, and NO redirects.
class CustomerController
{
    // One model instance (and therefore one DB connection) reused across
    // this controller's methods.
    private $customer;

    public function __construct()
    {
        $this->customer = new CustomerClass();
    }

    // Register a new customer. $data is an associative array of already
    // sanitised fields from the action file. Checks for a duplicate email
    // first, then inserts. Returns a structured result the action can act
    // on without knowing anything about the database.
    public function register($data)
    {
        if ($this->customer->emailExists($data['email'])) {
            return ['success' => false, 'error' => 'Email already registered.'];
        }

        $ok = $this->customer->addCustomer(
            $data['name'],
            $data['email'],
            $data['pass'],
            $data['country'],
            $data['city'],
            $data['contact']
        );

        if ($ok) {
            return ['success' => true];
        }
        return ['success' => false, 'error' => 'Registration failed. Please try again.'];
    }

    // Authenticate a login attempt. Returns the customer row on success,
    // or a structured error array on failure.
    public function login($email, $pass)
    {
        $row = $this->customer->login($email, $pass);
        if ($row) {
            return ['success' => true, 'customer' => $row];
        }
        return ['success' => false, 'error' => 'Invalid email or password.'];
    }
}
