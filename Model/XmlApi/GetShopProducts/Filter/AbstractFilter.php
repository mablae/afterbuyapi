<?php

namespace Wk\AfterbuyApiBundle\Model\XmlApi\GetShopProducts\Filter;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class AbstractFilter
 *
 * @Serializer\AccessorOrder("custom", custom={"filterName", "filterValues"})
 */
abstract class AbstractFilter
{
    /**
     * @Serializer\Type("array<string,string>")
     * @Serializer\XmlKeyValuePairs()
     * @Serializer\SerializedName("FilterValues")
     * @var array
     */
    protected $filterValues = [];

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("FilterName")
     * @var string
     */
    private $filterName;

    /**
     * @param string $filterName
     */
    public function __construct($filterName)
    {
        $this->filterName = $filterName;
    }

    /**
     * @return string
     */
    public function getFilterName()
    {
        return $this->filterName;
    }

    /**
     * Converts the object to an array
     *
     * @return array
     */
    public function __toArray()
    {
        return [$this->filterName => $this->filterValues];
    }

    /**
     * Converts the object to a string
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s: %s', $this->filterName, json_encode($this->filterValues));
    }
}

