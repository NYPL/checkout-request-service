<?php
namespace NYPL\Services\Model\CheckoutRequest;

use NYPL\Services\Model\CheckoutRequestModel;
use NYPL\Starter\Model\ModelTrait\TranslateTrait;

/**
 * @SWG\Definition(title="NewCheckoutRequest", type="object")
 *
 * @package NYPL\Services\Model\CheckoutRequest
 */
class NewCheckoutRequest extends CheckoutRequestModel
{
    use TranslateTrait;
}
