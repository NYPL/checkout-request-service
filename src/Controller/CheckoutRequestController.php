<?php
namespace NYPL\Services\Controller;

use NYPL\Services\CancelRequestLogger;
use NYPL\Services\CheckoutClient;
use NYPL\Services\JobService;
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
use NYPL\Starter\Config;

/**
 * Class CheckoutRequestController
 *
 * @package NYPL\Services\Controller
 */
class CheckoutRequestController extends ServiceController
{
  /**
   * @SWG\Post(
   *     path="/v0.1/checkout-requests-sync",
   *     summary="Process a checkout request",
   *     tags={"checkout-requests-sync"},
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
   *         response="406",
   *         description="Not accepted",
   *         @SWG\Schema(ref="#/definitions/CheckoutRequestErrorResponse")
   *     ),
   *     @SWG\Response(
   *         response="409",
   *         description="Conflict",
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

   public function createCheckoutRequestSync() {
     return $this->createCheckoutRequest();
   }
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
     *         response="406",
     *         description="Not accepted",
     *         @SWG\Schema(ref="#/definitions/CheckoutRequestErrorResponse")
     *     ),
     *     @SWG\Response(
     *         response="409",
     *         description="Conflict",
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
    public function createCheckoutRequest()
    {
        try {
            $data = $this->getRequest()->getParsedBody();
            APILogger::addDebug('POST request sent.', $data);
            // Need to randomize partner barcodes to avoid going over checkout limit
            $data = $this->reassignPartnerBarcode($data);
            $checkoutRequest = new CheckoutRequest($data);
            // Exclude checkoutJobId and processed values used for non-cancellation responses.
            $checkoutRequest->addExcludedProperties(['checkoutJobId', 'processed']);

            $this->initiateCheckoutRequest($checkoutRequest);

            // Assume success unless an error response is returned.
            $successFlag = true;
            $checkoutStatus = 200;

            $checkoutResponse = $this->sendCircOperation($checkoutRequest);

            if ($checkoutResponse instanceof CheckoutRequestErrorResponse) {
                $successFlag = false;
                $checkoutStatus = $checkoutResponse->getStatusCode();
            }

            $this->updateCheckoutRequest($checkoutRequest, $successFlag);

            return $this->getResponse()->withJson($checkoutResponse)->withStatus($checkoutStatus);

        } catch (APIException $exception) {
            APILogger::addError('NCIP exception thrown.', [$exception->getMessage()]);
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
     * @param CheckoutRequest $checkoutRequest
     * @return Response
     */
    protected function initiateCheckoutRequest(CheckoutRequest $checkoutRequest)
    {
        // Validate request data.
        try {
            $checkoutRequest->validatePostData();
        } catch (APIException $exception) {
            return $this->invalidRequestResponse($exception);
        }

        $checkoutRequest->create();

        // Initiate a job for non-cancellation requests.
        if (is_null($checkoutRequest->getJobId()) && $this->isUseJobService()) {
            $checkoutRequest->setCheckoutJobId(JobService::generateJobId($this->isUseJobService()));
            // Set jobId for proper responses for non-cancellation requests.
            $checkoutRequest->setJobId($checkoutRequest->getCheckoutJobId());
            APILogger::addDebug(
                'Initiating job via Job Service API ReCAP checkout request.',
                ['checkoutJobID' => $checkoutRequest->getCheckoutJobId()]
            );
            JobService::beginJob($checkoutRequest);
        }

        // Log start of general checkout requests or cancel request checkouts.
        $initLogMessage = 'Initiating checkout process.';
        if (is_int($checkoutRequest->getCancelRequestId())) {
            $initLogMessage .= ' (CancelRequestID: ' . $checkoutRequest->getCancelRequestId() . ')';
            CancelRequestLogger::addInfo($initLogMessage);
        }
    }

    /**
     * @param CheckoutRequest $checkoutRequest
     * @param bool            $successFlag
     */
    protected function updateCheckoutRequest(CheckoutRequest $checkoutRequest, bool $successFlag)
    {
        // Log updates for general checkout requests or cancel request checkouts.
        $updateLogMessage = 'Updating checkout request status.';
        if (is_int($checkoutRequest->getCancelRequestId())) {
            $updateLogMessage .= ' (CancelRequestID: ' . $checkoutRequest->getCancelRequestId() . ')';
            CancelRequestLogger::addInfo($updateLogMessage);
        }

        $checkoutRequest->update(
            ['success' => $successFlag]
        );

        // Finish job processing for non-cancellation requests.
        if (!is_null($checkoutRequest->getCheckoutJobId()) && $this->isUseJobService()) {
            APILogger::addDebug('Updating checkout job.', ['checkoutJobID' => $checkoutRequest->getCheckoutJobId()]);
            JobService::finishJob($checkoutRequest);
            // Add processed value back for non-cancellation responses.
            $checkoutRequest->removeExcludedProperties(['processed']);
        }
    }

    /**
     * @param CheckoutRequest $checkoutRequest
     * @return CheckoutRequestErrorResponse|CheckoutRequestResponse
     */
    protected function sendCircOperation(CheckoutRequest $checkoutRequest)
    {
        $checkoutClient = new CheckoutClient();
        $checkoutClientResponse = $checkoutClient->buildCheckoutRequest($checkoutRequest);

        APILogger::addDebug('API Response', $checkoutClientResponse);

        $checkoutRequest->addFilter(new Filter('id', $checkoutRequest->getId()));
        $checkoutRequest->read();

        $checkoutResponse = new CheckoutRequestResponse($checkoutRequest);

        if ($checkoutClientResponse instanceof CheckoutItemErrorResponse) {
            if ($checkoutClientResponse->getStatusCode() >= 400) {
                $checkoutResponse = new CheckoutRequestErrorResponse(
                    $checkoutClientResponse->getStatusCode(),
                    'ncip-checkout-error',
                    $checkoutClientResponse->getProblem()
                );
            }
        }
        $checkoutResponse->setStatusCode($checkoutClientResponse->getStatusCode());

        return $checkoutResponse;
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

        APILogger::addError(
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

    /**
     * @param array $data should have a patronBarcode attribute, which will be randomly reassigned for partners
     * @return array will be a copy of the input with patronBarcode reassigned in case it was originally a partner barcode
     */
    public function reassignPartnerBarcode($data)
    {
      $key = "PATRON_BARCODES_{$data['patronBarcode']}";
      $barcodes = explode("," , Config::get($key, "", true));
      $barcodes = array_filter($barcodes, function ($item) { return $item; });
      $numberOfPatronBarcodes = count($barcodes);
      if ($numberOfPatronBarcodes >= 1) {
        $newBarcode = $barcodes[rand(0, $numberOfPatronBarcodes - 1)];
        if ($newBarcode) {
          $data['patronBarcode'] = $newBarcode;
          APILogger::addDebug('Randomizing partner barcode', array('newBarcode' => $newBarcode));
        }
      }
      return $data;
    }
}
