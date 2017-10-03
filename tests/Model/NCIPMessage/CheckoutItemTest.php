<?php
namespace NYPL\Services\Test\Model\NCIPMessage;

use NYPL\Services\Model\CheckoutRequest\CheckoutRequest;
use NYPL\Services\Model\NCIPMessage\CheckoutItem;
use PHPUnit\Framework\TestCase;

class CheckoutItemTest extends TestCase
{
    public $checkoutRequest;
    public $checkoutItem;

    public function setUp()
    {
        parent::setUp();

        $data = json_decode(file_get_contents(__DIR__ . '/../../Stubs/validCheckoutRequest.json'), true);

        $this->checkoutRequest = new CheckoutRequest($data);
        $this->checkoutItem = new CheckoutItem($this->checkoutRequest);
    }

    /**
     * @covers NYPL\Services\Model\NCIPMessage\CheckoutItem::buildMessage()
     * @covers NYPL\Services\Model\NCIPMessage\CheckoutItem::initializeMessage()
     * @covers NYPL\Services\Model\NCIPMessage\CheckoutItem::replacePatronBarcode()
     * @covers NYPL\Services\Model\NCIPMessage\CheckoutItem::replaceItemBarcode()
     * @covers NYPL\Services\Model\NCIPMessage\CheckoutItem::replaceDesiredDateDue()
     */
    public function testIfMessageIsInitialized()
    {
        self::assertInstanceOf('\NYPL\Services\Model\NCIPMessage\CheckoutItem', $this->checkoutItem);
        self::assertInstanceOf('\Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement', $this->checkoutItem->message);
    }
}
