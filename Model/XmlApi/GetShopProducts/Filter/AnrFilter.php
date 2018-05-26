<?php

namespace Wk\AfterbuyApiBundle\Model\XmlApi\GetShopProducts\Filter;

/**
 * Class OrderIdFilter
 */
class AnrFilter extends AbstractFilter
{
    /**
     * @param int $artikelNr
     */
    public function __construct($artikelNr)
    {
        parent::__construct('Anr');

        $this->filterValues['FilterValue'] = (string)$artikelNr;
    }
}
