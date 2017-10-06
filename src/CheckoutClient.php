<?php
namespace NYPL\Services;

use NYPL\Services\Model\CheckoutRequest\CheckoutRequest;
use NYPL\Services\Model\NCIPMessage\CheckoutItem;
use NYPL\Services\Model\NCIPResponse\CheckoutItemResponse;
use NYPL\Starter\APIClient;
use NYPL\Starter\APILogger;

/**
 * Class CheckoutClient
 *
 * @package NYPL\Services
 */
class CheckoutClient extends APIClient
{
    /**
     * @var CheckoutItem
     */
    public $checkoutItem;

    /**
     * @return CheckoutItem
     */
    public function getCheckoutItem()
    {
        return $this->checkoutItem;
    }

    /**
     * @param mixed $checkoutItem
     */
    public function setCheckoutItem($checkoutItem)
    {
        $this->checkoutItem = $checkoutItem;
    }

    /**
     * @return bool
     */
    protected function isRequiresAuth()
    {
        return false;
    }

    /**
     * @param CheckoutRequest $checkoutRequest
     * @return NCIPResponse
     */
    public function buildCheckoutRequest(CheckoutRequest $checkoutRequest)
    {
        if (!$this->getCheckoutItem()) {
            $this->setCheckoutItem(new CheckoutItem($checkoutRequest));
        }

        APILogger::addDebug('NCIP Message', $this->getCheckoutItem()->getMessage()->asXML());

        $ncipResponse = $this->sendCheckoutRequest();

        return $ncipResponse;
    }

    /**
     * @return CheckoutItemResponse
     */
    protected function sendCheckoutRequest()
    {
        return NCIPClient::sendNCIPMessage($this->getCheckoutItem());
    }
}
