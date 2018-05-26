<?php

namespace Wk\AfterbuyApiBundle\Model\XmlApi\GetShopProducts;

use JMS\Serializer\Annotation as Serializer;
use Wk\AfterbuyApiBundle\Model\XmlApi\AbstractRequest;
use Wk\AfterbuyApiBundle\Model\XmlApi\AfterbuyGlobal;
use Wk\AfterbuyApiBundle\Model\XmlApi\GetShopProducts\Filter\AbstractFilter;

/**
 * Class GetSoldItemsRequest
 *
 * @Serializer\XmlRoot("Request")
 */
class GetShopProductsRequest extends AbstractRequest
{
    const CALL_NAME = 'GetShopProducts';

    /**
     * @Serializer\Type("boolean")
     * @Serializer\SerializedName("RequestAllItems")
     * @var bool
     */
    private $requestAllItems;

    /**
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("MaxSoldItems")
     * @var int
     */
    private $maxSoldItems;


    /**
     * @Serializer\Type("array<Wk\AfterbuyApiBundle\Model\XmlApi\GetShopProducts\Filter\AbstractFilter>")
     * @Serializer\XmlList(entry="Filter")
     * @Serializer\SerializedName("DataFilter")
     * @var AbstractFilter[]
     */
    private $filters = [];

    /**
     * @param AfterbuyGlobal $afterbuyGlobal
     */
    public function __construct(AfterbuyGlobal $afterbuyGlobal)
    {
        parent::__construct($afterbuyGlobal);

        $this->setCallName(self::CALL_NAME);
    }

    /**
     * @return bool
     */
    public function getRequestAllItems()
    {
        return $this->requestAllItems;
    }

    /**
     * @param bool $requestAllItems
     *
     * @return $this
     */
    public function setRequestAllItems($requestAllItems)
    {
        $this->requestAllItems = $requestAllItems;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxSoldItems()
    {
        return $this->maxSoldItems;
    }

    /**
     * @param int $maxSoldItems
     *
     * @return $this
     */
    public function setMaxSoldItems($maxSoldItems)
    {
        $this->maxSoldItems = $maxSoldItems;

        return $this;
    }

    /**
     * @return AbstractFilter[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param AbstractFilter[] $filters
     *
     * @return $this
     */
    public function setFilters(array $filters)
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * @param AbstractFilter $filter
     *
     * @return $this
     */
    public function addFilter(AbstractFilter $filter)
    {
        $this->filters[] = $filter;

        return $this;
    }
}
