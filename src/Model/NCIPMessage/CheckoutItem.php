<?php
namespace NYPL\Services\Model\NCIPMessage;

use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;
use NYPL\Services\Model\CheckoutRequest\CheckoutRequest;
use NYPL\Services\NCIPMessage;
use NYPL\Starter\Model\LocalDateTime;

/**
 * Class CheckoutItem
 *
 * @package NYPL\Services\Model\NCIPMessage
 */
class CheckoutItem extends NCIPMessage
{
    const XML_NAMESPACE = 'http://www.niso.org/2008/ncip';
    const XML_PREFIX = 'co';
    const XML_NODE = 'CheckOutItem';

    /**
     * @var string
     */
    public $patronBarcode = '';

    /**
     * @var string
     */
    public $itemBarcode = '';

    /**
     * @var LocalDateTime
     */
    public $desiredDateDue = '';

    /**
     * @var string|QuiteSimpleXMLElement
     */
    public $message = '';

    /**
     * @var string
     */
    public $xml = '';

    /**
     * CheckoutItem constructor.
     *
     * @param CheckoutRequest $checkoutRequest
     */
    public function __construct(CheckoutRequest $checkoutRequest)
    {
        if ($checkoutRequest->getPatronBarcode()) {
            $this->setPatronBarcode($checkoutRequest->getPatronBarcode());
        }

        if ($checkoutRequest->getItemBarcode()) {
            $this->setItemBarcode($checkoutRequest->getItemBarcode());
        }

        if ($checkoutRequest->getDesiredDateDue()) {
            $this->setDesiredDateDue($checkoutRequest->getDesiredDateDue());
        }

        $this->buildMessage();
    }

    /**
     * @return string
     */
    public function getXml()
    {
        if (!$this->xml) {
            $this->setXml(file_get_contents(__DIR__ . '/XML/CheckOutItem.xml'));
        }

        return $this->xml;
    }

    /**
     * @param string $xml
     */
    public function setXml($xml)
    {
        $this->xml = $xml;
    }

    /**
     * @return string
     */
    public function getPatronBarcode()
    {
        return $this->patronBarcode;
    }

    /**
     * @param string $patronBarcode
     */
    public function setPatronBarcode($patronBarcode)
    {
        $this->patronBarcode = $patronBarcode;
    }

    /**
     * @return string
     */
    public function getItemBarcode()
    {
        return $this->itemBarcode;
    }

    /**
     * @param string $itemBarcode
     */
    public function setItemBarcode($itemBarcode)
    {
        $this->itemBarcode = $itemBarcode;
    }

    /**
     * @return string
     */
    public function getDesiredDateDue()
    {
        return $this->desiredDateDue;
    }

    /**
     * @param string $desiredDateDue
     */
    public function setDesiredDateDue(LocalDateTime $desiredDateDue)
    {
        $this->desiredDateDue = $desiredDateDue;
    }

    /**
     * @return QuiteSimpleXMLElement
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param QuiteSimpleXMLElement $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function messageToString()
    {
        return $this->getMessage()->asXML();
    }

    /**
     * Retrieve the default NCIP message provided by third-party client.
     */
    protected function initializeMessage()
    {
        $xmlElem = new QuiteSimpleXMLElement($this->getXml());
        $xmlElem->registerXPathNamespace(self::XML_PREFIX, self::XML_NAMESPACE);

        $this->setMessage($xmlElem);
    }

    /**
     * Substitute default XML value with value sent in the checkout request.
     */
    protected function replacePatronBarcode()
    {
        $patronXml = $this->getMessage()->first(self::XML_PREFIX . ':' . self::XML_NODE . '/co:UserId/co:UserIdentifierValue');
        $patronXml->setValue($this->getPatronBarcode());
    }

    /**
     * Substitute default XML value with value sent in the checkout request.
     */
    protected function replaceItemBarcode()
    {
        $itemXml = $this->getMessage()->first(self::XML_PREFIX . ':' . self::XML_NODE . '/co:ItemId/co:ItemIdentifierValue');
        $itemXml->setValue($this->getItemBarcode());
    }

    /**
     * Substitute default XML value with value sent in the checkout request.
     */
    protected function replaceDesiredDateDue()
    {
        $dateDueXml = $this->getMessage()->first(self::XML_PREFIX . ':' . self::XML_NODE . '/co:DesiredDateDue');
        $dateDueXml->setValue($this->getDesiredDateDue());
    }

    /**
     * @return $this
     */
    protected function buildMessage()
    {
        $this->initializeMessage();

        $this->replacePatronBarcode();
        $this->replaceItemBarcode();
        $this->replaceDesiredDateDue();

        return $this;
    }
}
