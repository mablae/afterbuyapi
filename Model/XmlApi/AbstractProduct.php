<?php

namespace Wk\AfterbuyApiBundle\Model\XmlApi;

use DateTime;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Product
 */
abstract class AbstractProduct
{
    /**
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("ProductID")
     * @var int
     */
    protected $productId;

    /**
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("Anr")
     * @var int
     */
    protected $anr;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("EAN")
     * @var string
     */
    protected $ean;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Name")
     * @var string
     */
    protected $name;

    /**
     * @Serializer\Type("DateTime<'d.m.Y H:i:s', 'UTC', '!d.m.Y'>")
     * @Serializer\SerializedName("ModDate")
     * @var \DateTime
     */
    protected $modDate;


    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("ShortDescription")
     * @var string
     */
    protected $shortDescription;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Memo")
     * @var string
     */
    protected $memo;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Description")
     * @var string
     */
    protected $description;

    /**
     * @Serializer\Type("float")
     * @Serializer\SerializedName("SellingPrice")
     * @var string
     */
    protected $sellingPrice;

    /**
     * @Serializer\Type("float")
     * @Serializer\SerializedName("TaxRate")
     * @var string
     */
    protected $taxRate;

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @return int
     */
    public function getAnr()
    {
        return $this->anr;
    }

    /**
     * @return string
     */
    public function getEan()
    {
        return $this->ean;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return DateTime
     */
    public function getModDate()
    {
        return $this->modDate;
    }

    /**
     * @return string
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * @return string
     */
    public function getMemo()
    {
        return $this->memo;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getSellingPrice()
    {
        return $this->sellingPrice;
    }

    /**
     * @return string
     */
    public function getTaxRate()
    {
        return $this->taxRate;
    }




}
