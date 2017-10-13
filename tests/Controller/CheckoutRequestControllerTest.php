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
                $this->setUseJobService(1);
                parent::__construct($container, $cacheSeconds);
            }

            public function createCheckoutRequest()
            {
                return parent::createCheckoutRequest();
            }

        };
    }

    /**
     * @covers NYPL\Services\Controller\CheckoutRequestController::processCheckoutRequest()
     */
    public function testCreatesCheckOutModelFromRequest()
    {
        $controller = $this->fakeCheckoutController;

        $response = $controller->createCheckoutRequest();

        self::assertInstanceOf('Slim\Http\Response', $response);
    }

    /**
     * @covers NYPL\Services\Controller\CheckoutRequestController::processCheckoutRequest()
     */
    public function testMisconfigurationThrowsException()
    {
        $controller = $this->fakeCheckoutController;

        $response = $controller->createCheckoutRequest();

        self::assertSame(500, $response->getStatusCode());
    }
}
