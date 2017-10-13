<?php
namespace NYPL\Services\Model;

use NYPL\Starter\Model;
use NYPL\Starter\Model\LocalDateTime;
use NYPL\Starter\Model\ModelTrait\TranslateTrait;

/**
 * Class CheckoutRequestModel
 *
 * @package \NYPL\Services\Model
 */
class CheckoutRequestModel extends Model
{
    use TranslateTrait;

    /**
     * @SWG\Property(example="1234567890")
     * @var string
     */
    public $patronBarcode;

    /**
     * @SWG\Property(example="1234567890")
     * @var string
     */
    public $itemBarcode;

    /**
     * @SWG\Property(example="NYPL")
     * @var string
     */
    public $owningInstitutionId;

    /**
     * @SWG\Property(example="2018-01-07T02:32:51Z", type="string")
     * @var LocalDateTime
     */
    public $desiredDateDue;

    /**
     * @SWG\Property(example="1234567890")
     * @var int
     */
    public $cancelRequestId;

    /**
     * @SWG\Property(example="5aaa1212cd")
     * @var string
     */
    public $jobId;

    /**
     * @return string
     */
    public function getItemBarcode()
    {
        return $this->itemBarcode;
    }

    /**
     * @param string $itemBarcode
     */
    public function setItemBarcode($itemBarcode)
    {
        $this->itemBarcode = $itemBarcode;
    }

    /**
     * @return string
     */
    public function getPatronBarcode()
    {
        return $this->patronBarcode;
    }

    /**
     * @param string $patronBarcode
     */
    public function setPatronBarcode($patronBarcode)
    {
        $this->patronBarcode = $patronBarcode;
    }

    /**
     * @return string
     */
    public function getOwningInstitutionId()
    {
        return $this->owningInstitutionId;
    }

    /**
     * @param string $owningInstitutionId
     */
    public function setOwningInstitutionId($owningInstitutionId)
    {
        $this->owningInstitutionId = $owningInstitutionId;
    }

    /**
     * @return LocalDateTime
     */
    public function getDesiredDateDue()
    {
        return $this->desiredDateDue;
    }

    /**
     * @param LocalDateTime $desiredDateDue
     */
    public function setDesiredDateDue($desiredDateDue)
    {
        $this->desiredDateDue = $desiredDateDue;
    }

    /**
     * @param string $desiredDateDue
     *
     * @return LocalDateTime
     */
    public function translateDesiredDateDue($desiredDateDue = '')
    {
        return new LocalDateTime(LocalDateTime::FORMAT_DATE_TIME_RFC, $desiredDateDue);
    }

    /**
     * @return int
     */
    public function getCancelRequestId()
    {
        return $this->cancelRequestId;
    }

    /**
     * @param int $cancelRequestId
     */
    public function setCancelRequestId($cancelRequestId)
    {
        $this->cancelRequestId = (int)$cancelRequestId;
    }

    /**
     * @return string
     */
    public function getJobId()
    {
        return $this->jobId;
    }

    /**
     * @param string $jobId
     */
    public function setJobId($jobId)
    {
        $this->jobId = $jobId;
    }

    /**
     * @param array $properties
     */
    public function addExcludedProperties(array $properties)
    {
        $excluded = $this->getExcludeProperties();
        $excluded = array_merge($excluded, $properties);
        $this->setExcludeProperties($excluded);
    }

    /**
     * @param array $properties
     */
    public function removeExcludedProperties(array $properties)
    {
        $excluded = $this->getExcludeProperties();
        $excluded = array_diff($excluded, $properties);
        $this->setExcludeProperties($excluded);
    }
}
