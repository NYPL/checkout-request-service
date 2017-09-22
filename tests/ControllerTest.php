<?php
namespace NYPL\Services\Test;

use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase
{
    public $checkInRequestModel;
    public $checkOutRequestModel;
    public $updateCheckInRequestModel;

    public function setUp()
    {
        $this->checkInRequestModel = new CheckInRequestModel();
        $this->checkOutRequestModel = new CheckOutRequestModel();
        $this->updateCheckInRequestModel = new UpdateCheckInRequestModel();
    }

    public function testCreatesCheckInModelFromRequest()
    {

    }

    public function testCreatesCheckOutModelFromRequest()
    {

    }

    public function testCreatesUpdateCheckInModelFromRequest()
    {

    }
}
