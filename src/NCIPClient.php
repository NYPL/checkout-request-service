<?php
namespace NYPL\Services;

use Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement;
use GuzzleHttp\Client;
use NYPL\Services\Model\NCIPResponse\CheckoutItemErrorResponse;
use NYPL\Services\Model\NCIPResponse\CheckoutItemResponse;
use NYPL\Starter\APILogger;
use NYPL\Starter\Config;

/**
 * Class NCIPClient
 *
 * @package NYPL\Services
 */
class NCIPClient
{
    const XML_PREFIX = 'co';
    const XML_NAMESPACE = 'http://www.niso.org/2008/ncip';
    const XML_RESPONSE_NODE = 'CheckOutItemResponse';
    const CLIENT_TIMEOUT = 20;

    /**
     * @var Client
     */
    public static $client;

    /**
     * @var NCIPMessage
     */
    public static $checkOutItem;

    /**
     * @var \SimpleXMLElement
     */
    protected static $response;

    /**
     * @var
     */
    protected static $ncipResponse;

    /**
     * @var array
     */
    public static $problemResponses = [
        '9025' => '208',
        '5000' => '400',
        '9016' => '404',
    ];

    /**
     * @return \GuzzleHttp\Client
     */
    public static function getClient()
    {
        return self::$client;
    }

    /**
     * @param \GuzzleHttp\Client $client
     */
    public static function setClient($client)
    {
        self::$client = $client;
    }

    /**
     * @return mixed
     */
    public static function getCheckOutItem()
    {
        return self::$checkOutItem;
    }

    /**
     * @param mixed $checkOutItem
     */
    public static function setCheckOutItem($checkOutItem)
    {
        self::$checkOutItem = $checkOutItem;
    }

    /**
     * @return \SimpleXMLElement
     */
    public static function getResponse()
    {
        return self::$response;
    }

    /**
     * @param \SimpleXMLElement $response
     */
    public static function setResponse($response)
    {
        self::$response = $response;
    }

    /**
     * @return mixed
     */
    public static function getNcipResponse()
    {
        return self::$ncipResponse;
    }

    /**
     * @param mixed $ncipResponse
     */
    public static function setNcipResponse($ncipResponse)
    {
        self::$ncipResponse = $ncipResponse;
    }

    public static function initializeClient()
    {
        self::setClient(new Client([
            'base_uri' => Config::get('NCIP_URL'),
            'timeout' => self::CLIENT_TIMEOUT,
            'headers' => [
                'Content-Type' => 'application/xml'
            ]
        ]));
    }

    /**
     * @param NCIPMessage $ncipMessage
     * @return CheckoutItemErrorResponse|CheckoutItemResponse
     */
    public static function sendNCIPMessage(NCIPMessage $ncipMessage)
    {
        self::initializeClient();

        APILogger::addDebug('message', [$ncipMessage->messageToString()]);

        $response = self::getClient()->post(
            '',
            [
                'body' => $ncipMessage->messageToString()
            ]
        );

        self::setResponse(new \SimpleXMLElement((string)$response->getBody()));

        APILogger::addDebug('ncipResponse', [self::getResponse()]);

        // Handle the XML Response.
        self::processNCIPResponse();

        return self::getNcipResponse();
    }

    /**
     * Determine a success or error response from the XML response body.
     */
    protected static function processNCIPResponse()
    {
        $xmlElem = new QuiteSimpleXMLElement(self::getResponse());
        $xmlElem->registerXPathNamespace(self::XML_PREFIX, self::XML_NAMESPACE);

        if ($xmlElem->has(self::XML_PREFIX . ':' . self::XML_RESPONSE_NODE . '/co:Problem')) {
            $problemType = $xmlElem->text(self::XML_PREFIX . ':' . self::XML_RESPONSE_NODE . '/co:Problem/co:ProblemType');
            $problemDetail = $xmlElem->text(self::XML_PREFIX . ':' . self::XML_RESPONSE_NODE . '/co:Problem/co:ProblemDetail');

            // Log the problem.
            APILogger::addDebug('NCIP Error Message: ' . $problemDetail);

            $errorResponse = new CheckoutItemErrorResponse(self::getResponse());

            if (array_key_exists($problemType, self::$problemResponses)) {
                $errorResponse->setStatusCode(self::$problemResponses[$problemType]);
            }
            $errorResponse->setProblem($problemDetail);

            self::setNcipResponse($errorResponse);
        } else {
            self::setNcipResponse(new CheckoutItemResponse(self::getResponse()));
        }
    }
}
