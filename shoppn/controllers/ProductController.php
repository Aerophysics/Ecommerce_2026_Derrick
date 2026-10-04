<?php

require_once __DIR__ . "/../classes/ProductClass.php";

/**
 * ProductController bridges action files and views with the ProductClass model.
 * Contains no HTML, direct SQL, or redirects.
 */
class ProductController
{
    private $product;

    public function __construct()
    {
        $this->product = new ProductClass();
    }

    /**
     * Expose model method to add a brand.
     *
     * @param string $name
     * @return bool
     */
    public function addBrand($name)
    {
        return $this->product->addBrand($name);
    }

    /**
     * Expose model method to get all brands.
     *
     * @return array
     */
    public function getAllBrands()
    {
        return $this->product->getAllBrands();
    }

    /**
     * Expose model method to get a single brand by ID.
     *
     * @param int $id
     * @return array|false
     */
    public function getBrandById($id)
    {
        return $this->product->getBrandById($id);
    }

    /**
     * Expose model method to update a brand.
     *
     * @param int $id
     * @param string $name
     * @return bool
     */
    public function updateBrand($id, $name)
    {
        return $this->product->updateBrand($id, $name);
    }

    // ============================================================
    // Category Methods (Task 7 & Task 8)
    // ============================================================

    /**
     * Expose model method to add a category.
     *
     * @param string $name
     * @return bool
     */
    public function addCategory($name)
    {
        return $this->product->addCategory($name);
    }

    /**
     * Expose model method to get all categories.
     *
     * @return array
     */
    public function getAllCategories()
    {
        return $this->product->getAllCategories();
    }

    /**
     * Expose model method to get a single category by ID.
     *
     * @param int $id
     * @return array|false
     */
    public function getCategoryById($id)
    {
        return $this->product->getCategoryById($id);
    }

    /**
     * Expose model method to update a category.
     *
     * @param int $id
     * @param string $name
     * @return bool
     */
    public function updateCategory($id, $name)
    {
        return $this->product->updateCategory($id, $name);
    }
}
