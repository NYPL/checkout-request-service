<?php
namespace NYPL\Services\Test;

use NYPL\Services\Model\CheckoutRequest\CheckoutRequest;
use NYPL\Services\Model\NCIPMessage\CheckoutItem;
use NYPL\Services\CheckoutClient;
use NYPL\Services\NCIPClient;
use NYPL\Services\NCIPMessage;
use NYPL\Services\Test\Mocks\MockConfig;
use PHPUnit\Framework\TestCase;

class CheckoutClientTest extends TestCase
{
    public $checkoutRequest;
    public $checkoutItem;
    public $checkoutClient;
    public $fakeNCIPClient;

    public function setUp()
    {
        MockConfig::initialize(__DIR__ . '/../');

        $data = json_decode(file_get_contents(__DIR__ . '/Stubs/validCheckoutRequest.json'), true);
        $this->checkoutRequest = new CheckoutRequest($data);
        $this->checkoutItem = new CheckoutItem($this->checkoutRequest);
        $this->checkoutClient = new CheckoutClient();

        $this->fakeNCIPClient = new class extends NCIPClient  {
            public static function sendNCIPMessage(NCIPMessage $ncipMessage)
            {
                self::initializeClient();

                $response = self::getClient()->post(
                    '',
                    [
                        'body' => $ncipMessage->messageToString()
                    ]
                );

                self::setResponse(new \SimpleXMLElement((string)$response->getBody()));

                // Handle the XML Response.
                self::processNCIPResponse();

                return self::getNcipResponse();
            }

            public static function processNCIPResponse()
            {
                // Test for problems in NCIP response.
                // Set error response.
                // Set response.
            }
        };
    }

    public function testIfNCIPMessageIsSet()
    {
        self::assertObjectHasAttribute('xml', $this->checkoutItem);
    }

//    public function testIfNCIPResponseIsReturned()
//    {
//        $response = $this->checkoutClient->processCheckoutRequest($this->checkoutRequest);
//        self::assertInstanceOf('\NYPL\Services\Model\NCIPResponse', $response);
//    }
}
