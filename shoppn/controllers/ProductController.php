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

    // ============================================================
    // Product Customer-Facing Methods (Task 10)
    // ============================================================

    /**
     * Expose model method to get featured products.
     *
     * @param int $limit Max number of products to return (default 6)
     * @return array
     */
    public function getFeaturedProducts($limit = 6)
    {
        return $this->product->getFeaturedProducts($limit);
    }

    /**
     * Expose model method to get products by category.
     *
     * @param int $cat_id
     * @return array
     */
    public function getProductsByCategory($cat_id)
    {
        return $this->product->getProductsByCategory($cat_id);
    }

    /**
     * Expose model method to get products by brand.
     *
     * @param int $brand_id
     * @return array
     */
    public function getProductsByBrand($brand_id)
    {
        return $this->product->getProductsByBrand($brand_id);
    }

    /**
     * Expose model method to search products by keyword.
     *
     * @param string $query
     * @return array
     */
    public function searchProducts($query)
    {
        return $this->product->searchProducts($query);
    }

    /**
     * Expose model method to get a single product by ID.
     *
     * @param int $id
     * @return array|false
     */
    public function getProductById($id)
    {
        return $this->product->getProductById($id);
    }
}
