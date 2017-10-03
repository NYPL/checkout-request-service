<?php
namespace NYPL\Services\Controller;

use NYPL\Services\CancelRequestLogger;
use NYPL\Services\CheckoutClient;
use NYPL\Services\Model\CheckoutRequest\CheckoutRequest;
use NYPL\Services\Model\NCIPResponse\CheckoutItemErrorResponse;
use NYPL\Services\Model\Response\CheckoutRequestErrorResponse;
use NYPL\Services\Model\Response\CheckoutRequestResponse;
use NYPL\Services\ServiceController;
use NYPL\Starter\APIException;
use NYPL\Starter\APILogger;
use NYPL\Starter\Filter;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class CheckoutRequestController
 *
 * @package NYPL\Services\Controller
 */
class CheckoutRequestController extends ServiceController
{

    /**
     * @SWG\Post(
     *     path="/v0.1/checkout-requests",
     *     summary="Process a checkout request",
     *     tags={"checkout-requests"},
     *     operationId="processCheckoutRequest",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="NewCheckoutRequest",
     *         in="body",
     *         description="Request object based on the included data model",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/NewCheckoutRequest")
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful operation",
     *         @SWG\Schema(ref="#/definitions/CheckoutRequestResponse")
     *     ),
     *     @SWG\Response(
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Not found",
     *         @SWG\Schema(ref="#/definitions/CheckoutRequestErrorResponse")
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="Generic server error",
     *         @SWG\Schema(ref="#/definitions/CheckoutRequestErrorResponse")
     *     ),
     *     security={
     *         {
     *             "api_auth": {"openid offline_access api write:hold_request readwrite:hold_request"}
     *         }
     *     }
     * )
     *
     * @throws APIException
     * @return Response
     */
    public function processCheckoutRequest()
    {
        try {
            if (!$this->isRequestAuthorized()) {
                APILogger::addError('Invalid request received. Client not authorized to initiate checkout requests.');
                return $this->invalidScopeResponse(new APIException(
                    'Client not authorized to initiate checkout requests.',
                    null,
                    0,
                    null,
                    403
                ));
            }

            $data = $this->getRequest()->getParsedBody();

            $checkoutRequest = new CheckoutRequest($data);

            APILogger::addDebug('POST request sent.', $data);

            try {
                $checkoutRequest->validatePostData();
            } catch (APIException $exception) {
                return $this->invalidRequestResponse($exception);
            }

            $checkoutRequest->create();

            // Validate request data.
            // Send CheckoutRequest to client.
            CancelRequestLogger::addInfo('Initiating checkout process.');
            $checkoutClient = new CheckoutClient();

            $checkoutResponse = $checkoutClient->processCheckoutRequest($checkoutRequest);

            APILogger::addDebug('API Response', $checkoutResponse);

            $checkoutRequest->addFilter(new Filter('id', $checkoutRequest->getId()));
            $checkoutRequest->read();

            // Assume success unless an error response is returned.
            $successFlag = true;

            $response = new CheckoutRequestResponse($checkoutRequest);
            $responseStatus = 200;

            if ($checkoutResponse instanceof CheckoutItemErrorResponse) {
                if ($checkoutResponse->getStatusCode() >= 400) {
                    $response = new CheckoutRequestErrorResponse(
                        $checkoutResponse->getStatusCode(),
                        'ncip-checkout-error',
                        $checkoutResponse->getProblem()
                    );
                    $successFlag = false;
                    $responseStatus = $checkoutResponse->getStatusCode();
                }
            }

            $response->setStatusCode($checkoutResponse->getStatusCode());

            CancelRequestLogger::addInfo('Updating checkout request status.');
            $checkoutRequest->update(
                ['success' => $successFlag]
            );

            return $this->getResponse()->withJson($response)->withStatus($responseStatus);

        } catch (APIException $exception) {
            APILogger::addDebug('NCIP exception thrown.', [$exception->getMessage()]);
            return $this->getResponse()->withJson(new CheckoutRequestErrorResponse(
                400,
                'ncip-checkout-error',
                'NCIP exception thrown',
                $exception
            ))->withStatus(400);

        } catch (\Exception $exception) {
            $errorType = 'process-checkout-request-error';
            $errorMsg = 'Unable to process checkout request due to a problem with dependent services.';

            return $this->processException($errorType, $errorMsg, $exception, $this->getRequest());
        }
    }

    /**
     * @param string     $errorType
     * @param string     $errorMessage
     * @param \Exception $exception
     * @param Request    $request
     * @return \Slim\Http\Response
     */
    protected function processException($errorType, $errorMessage, \Exception $exception, Request $request)
    {
        $statusCode = 500;
        if ($exception instanceof APIException) {
            $statusCode = $exception->getHttpCode();
        }

        APILogger::addLog(
            $statusCode,
            get_class($exception) . ': ' . $exception->getMessage(),
            [
                $request->getHeaderLine('X-NYPL-Log-Stream-Name'),
                $request->getHeaderLine('X-NYPL-Request-ID'),
                (string) $request->getUri(),
                $request->getParsedBody()
            ]
        );

        if ($exception instanceof APIException) {
            if ($exception->getPrevious()) {
                $exception->setDebugInfo($exception->getPrevious()->getMessage());
            }
            APILogger::addDebug('APIException debug info.', [$exception->debugInfo]);
        }

        $errorResp = new CheckoutRequestErrorResponse(
            $statusCode,
            $errorType,
            $errorMessage,
            $exception
        );

        return $this->getResponse()->withJson($errorResp)->withStatus($statusCode);
    }
}
