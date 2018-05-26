<?php

namespace Wk\AfterbuyApiBundle\Model\XmlApi\GetShopProducts;

use JMS\Serializer\Annotation as Serializer;
use Wk\AfterbuyApiBundle\Model\XmlApi\Result as BaseResult;

/**
 * Class Result
 */
class Result extends BaseResult
{
    /**
     * @Serializer\Type("boolean")
     * @Serializer\SerializedName("HasMoreProducts")
     * @var bool
     */
    private $hasMoreProducts;

    /**
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("LastProductID")
     * @var int
     */
    private $lastProductId;


    /**
     * @Serializer\Type("array<Wk\AfterbuyApiBundle\Model\XmlApi\GetShopProducts\Product>")
     * @Serializer\SerializedName("Products")
     * @Serializer\XmlList(entry="Product")
     * @var Product[]
     */
    private $products = [];

    /**
     * @return bool
     */
    public function isHasMoreProducts()
    {
        return $this->hasMoreProducts;
    }

    /**
     * @return int
     */
    public function getLastProductId()
    {
        return $this->lastProductId;
    }

    /**
     * @return Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }
}
