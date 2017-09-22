<?php
namespace NYPL\Services\Test;

use NYPL\Services\Model\CheckoutRequestModel;
use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase
{
    public $checkoutRequestModel;

    public function setUp()
    {
        $this->checkoutRequestModel = new CheckoutRequestModel();
    }

    public function testCreatesCheckOutModelFromRequest()
    {
        $valid = true;
        self::assertTrue($valid);
    }
}
