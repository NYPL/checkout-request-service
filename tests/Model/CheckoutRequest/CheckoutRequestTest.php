<?php
namespace NYPL\Services\Test\Model;

use PHPUnit\Framework\TestCase;
use NYPL\Services\Model\CheckoutRequest\CheckoutRequest;

class CheckoutRequestTest extends TestCase
{
    public $checkoutRequest;
    public $schema;

    public function setUp()
    {
        $this->checkoutRequest = new CheckoutRequest();
        $this->schema = $this->checkoutRequest->getSchema();
    }

    /**
     * @covers NYPL\Services\Model\CheckoutRequest\CheckoutRequest::getSchema()
     */
    public function testIfSchemaHasValidKeys()
    {
        self::assertArrayHasKey('name', $this->schema);
        self::assertArrayHasKey('type', $this->schema);
        self::assertArrayHasKey('fields', $this->schema);
    }

    /**
     * @covers NYPL\Services\Model\CheckoutRequest\CheckoutRequest::getSchema()
     */
    public function testIfObjectContainsSchemaFields()
    {
        $fields = $this->schema['fields'];

        foreach ($fields as $field) {
            self::assertClassHasAttribute($field['name'], 'NYPL\Services\Model\CheckoutRequest\CheckoutRequest');
        }
    }

    /**
     * @covers NYPL\Services\Model\CheckoutRequest\CheckoutRequest::setId()
     * @covers NYPL\Services\Model\CheckoutRequest\CheckoutRequest::getId()
     * @covers NYPL\Services\Model\CheckoutRequest\CheckoutRequest::setItemBarcode()
     * @covers NYPL\Services\Model\CheckoutRequest\CheckoutRequest::getItemBarcode()
     * @covers NYPL\Services\Model\CheckoutRequest\CheckoutRequest::setPatronBarcode()
     * @covers NYPL\Services\Model\CheckoutRequest\CheckoutRequest::getPatronBarcode()
     * @covers NYPL\Services\Model\CheckoutRequest\CheckoutRequest::setCancelRequestId()
     * @covers NYPL\Services\Model\CheckoutRequest\CheckoutRequest::getCancelRequestId()
     * @covers NYPL\Services\Model\CheckoutRequest\CheckoutRequest::setJobId()
     * @covers NYPL\Services\Model\CheckoutRequest\CheckoutRequest::setOwningInstitutionId()
     * @covers NYPL\Services\Model\CheckoutRequest\CheckoutRequest::setDesiredDateDue()
     * @covers NYPL\Services\Model\CheckoutRequest\CheckoutRequest::validatePostData()
     */
    public function testIfPostDataIsValid()
    {
        $data = json_decode(file_get_contents(__DIR__ . '/../../Stubs/validCheckoutRequest.json'), true);

        $newRequest = new CheckoutRequest($data);

        self::assertInstanceOf('\NYPL\Services\Model\CheckoutRequest\CheckoutRequest', $newRequest);
    }

    /**
     * @expectedException \NYPL\Starter\APIException
     * @covers NYPL\Services\Model\CheckoutRequest\CheckoutRequest::validatePostData()
     */
    public function testIfInvalidPostDataThrowsException()
    {
        $this->checkoutRequest->setJobId('abcdefg');

        $this->checkoutRequest->validatePostData();
    }
}
