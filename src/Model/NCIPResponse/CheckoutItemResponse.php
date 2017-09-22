<?php
namespace NYPL\Services\Model\NCIPResponse;

use NYPL\Services\NCIPResponse;

/**
 * Class CheckoutItemResponse
 *
 * @package NYPL\Services\Model\NCIPResponse
 */
class CheckoutItemResponse extends NCIPResponse
{
    /**
     * @var int
     */
    public $statusCode = 202;

    /**
     * CheckoutItemResponse constructor.
     *
     * @param \SimpleXMLElement $xml
     */
    public function __construct(\SimpleXMLElement $xml)
    {
        $this->setXml($xml);
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return string
     */
    public function parse()
    {
        return $this->getXml()->asXML();
    }
}
