<?php

require_once __DIR__ . "/../core/db_class.php";

/**
 * ProductClass handles data operations for products and brands.
 * Extends the base Database class for prepared statement execution.
 */
class ProductClass extends Database
{
    /**
     * Insert a new product brand into the brands table.
     *
     * @param string $name Brand name
     * @return bool True on success, false on failure
     */
    public function addBrand($name)
    {
        $sql = "INSERT INTO brands (brand_name) VALUES (?)";
        return $this->execute($sql, [$name]);
    }

    /**
     * Retrieve all brands sorted alphabetically by brand_name.
     *
     * @return array Array of brand associative arrays
     */
    public function getAllBrands()
    {
        $sql = "SELECT * FROM brands ORDER BY brand_name ASC";
        return $this->fetchAll($sql);
    }

    /**
     * Retrieve a single brand by its primary key ID.
     *
     * @param int $id Brand ID
     * @return array|false Row associative array or false if not found
     */
    public function getBrandById($id)
    {
        $sql = "SELECT * FROM brands WHERE brand_id = ?";
        $row = $this->fetchOne($sql, [$id]);
        return $row ?: false;
    }

    /**
     * Update an existing brand's name.
     *
     * @param int $id Brand ID
     * @param string $name New brand name
     * @return bool True on success, false on failure
     */
    public function updateBrand($id, $name)
    {
        $sql = "UPDATE brands SET brand_name = ? WHERE brand_id = ?";
        return $this->execute($sql, [$name, $id]);
    }

    // ============================================================
    // Category Methods (Task 7 & Task 8)
    // ============================================================

    /**
     * Insert a new product category into the categories table.
     *
     * @param string $name Category name
     * @return bool True on success, false on failure
     */
    public function addCategory($name)
    {
        $sql = "INSERT INTO categories (cat_name) VALUES (?)";
        return $this->execute($sql, [$name]);
    }

    /**
     * Retrieve all categories sorted alphabetically by cat_name.
     *
     * @return array Array of category associative arrays
     */
    public function getAllCategories()
    {
        $sql = "SELECT * FROM categories ORDER BY cat_name ASC";
        return $this->fetchAll($sql);
    }

    /**
     * Retrieve a single category by its primary key ID.
     *
     * @param int $id Category ID
     * @return array|false Row associative array or false if not found
     */
    public function getCategoryById($id)
    {
        $sql = "SELECT * FROM categories WHERE cat_id = ?";
        $row = $this->fetchOne($sql, [$id]);
        return $row ?: false;
    }

    /**
     * Update an existing category's name.
     *
     * @param int $id Category ID
     * @param string $name New category name
     * @return bool True on success, false on failure
     */
    public function updateCategory($id, $name)
    {
        $sql = "UPDATE categories SET cat_name = ? WHERE cat_id = ?";
        return $this->execute($sql, [$name, $id]);
    }

    // ============================================================
    // Product Customer-Facing Methods (Task 10)
    // ============================================================

    /**
     * Retrieve featured products chosen randomly up to the given limit.
     * Mirrors the original getPro() function.
     *
     * @param int $limit Max number of products to return (default 6)
     * @return array Array of product rows (empty array if no results)
     */
    public function getFeaturedProducts($limit = 6)
    {
        $sql = "SELECT p.*, c.cat_name, b.brand_name 
                FROM products p
                LEFT JOIN categories c ON p.product_cat = c.cat_id
                LEFT JOIN brands b ON p.product_brand = b.brand_id
                ORDER BY RAND() LIMIT ?";
        $rows = $this->fetchAll($sql, [(int) $limit]);
        return is_array($rows) ? $rows : [];
    }

    /**
     * Retrieve products belonging to a specific category.
     *
     * @param int $cat_id Category ID
     * @return array Array of product rows (empty array if no results)
     */
    public function getProductsByCategory($cat_id)
    {
        $sql = "SELECT p.*, c.cat_name, b.brand_name 
                FROM products p
                LEFT JOIN categories c ON p.product_cat = c.cat_id
                LEFT JOIN brands b ON p.product_brand = b.brand_id
                WHERE p.product_cat = ?";
        $rows = $this->fetchAll($sql, [(int) $cat_id]);
        return is_array($rows) ? $rows : [];
    }

    /**
     * Retrieve products belonging to a specific brand.
     *
     * @param int $brand_id Brand ID
     * @return array Array of product rows (empty array if no results)
     */
    public function getProductsByBrand($brand_id)
    {
        $sql = "SELECT p.*, c.cat_name, b.brand_name 
                FROM products p
                LEFT JOIN categories c ON p.product_cat = c.cat_id
                LEFT JOIN brands b ON p.product_brand = b.brand_id
                WHERE p.product_brand = ?";
        $rows = $this->fetchAll($sql, [(int) $brand_id]);
        return is_array($rows) ? $rows : [];
    }

    /**
     * Search products by keyword in product_title or product_keywords.
     *
     * @param string $query Search keyword
     * @return array Array of product rows (empty array if no results)
     */
    public function searchProducts($query)
    {
        $term = '%' . $query . '%';
        $sql = "SELECT p.*, c.cat_name, b.brand_name 
                FROM products p
                LEFT JOIN categories c ON p.product_cat = c.cat_id
                LEFT JOIN brands b ON p.product_brand = b.brand_id
                WHERE p.product_title LIKE ? OR p.product_keywords LIKE ?";
        $rows = $this->fetchAll($sql, [$term, $term]);
        return is_array($rows) ? $rows : [];
    }

    /**
     * Retrieve a single product by its primary key ID.
     *
     * @param int $id Product ID
     * @return array|false Row associative array or false if not found
     */
    public function getProductById($id)
    {
        $sql = "SELECT p.*, c.cat_name, b.brand_name 
                FROM products p
                LEFT JOIN categories c ON p.product_cat = c.cat_id
                LEFT JOIN brands b ON p.product_brand = b.brand_id
                WHERE p.product_id = ?";
        $row = $this->fetchOne($sql, [(int) $id]);
        return $row ?: false;
    }
}
