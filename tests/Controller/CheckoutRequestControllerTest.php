<?php
namespace NYPL\Services\Test\Controller;

use NYPL\Services\Controller\CheckoutRequestController;
use NYPL\Services\Test\Mocks\MockConfig;
use NYPL\Services\Test\Mocks\MockService;
use PHPUnit\Framework\TestCase;

class CheckoutRequestControllerTest extends TestCase
{
    public $fakeCheckoutController;

    public function setUp()
    {
        parent::setUp();
        MockConfig::initialize(__DIR__ . '/../../');
        MockService::setMockContainer();
        $this->mockContainer = MockService::getMockContainer();

        $this->fakeCheckoutController = new class(MockService::getMockContainer(), 0) extends CheckoutRequestController {

            public $container;
            public $cacheSeconds;

            public function __construct(\Slim\Container $container, $cacheSeconds)
            {
                parent::__construct($container, $cacheSeconds);
            }

            public function processCheckoutRequest()
            {
                return parent::processCheckoutRequest();
            }

        };
    }

    public function testCreatesCheckOutModelFromRequest()
    {
        $controller = $this->fakeCheckoutController;

        $response = $controller->processCheckoutRequest();

        self::assertInstanceOf('Slim\Http\Response', $response);
    }

    public function testMisconfigurationThrowsException()
    {
        $controller = $this->fakeCheckoutController;

        $response = $controller->processCheckoutRequest();

        self::assertSame(400, $response->getStatusCode());
    }
}
