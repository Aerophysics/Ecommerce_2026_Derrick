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
}
