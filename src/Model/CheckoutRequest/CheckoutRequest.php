<?php
namespace NYPL\Services\Model\CheckoutRequest;

use NYPL\Starter\Model\LocalDateTime;
use NYPL\Starter\Model\ModelInterface\ReadInterface;
use NYPL\Starter\Model\ModelTrait\DBCreateTrait;
use NYPL\Starter\Model\ModelTrait\DBReadTrait;
use NYPL\Starter\Model\ModelTrait\DBUpdateTrait;

/**
 * @SWG\Definition(title="CheckoutRequest", type="object")
 *
 * @package NYPL\Services\Model\CheckoutRequest
 */
class CheckoutRequest extends NewCheckoutRequest implements ReadInterface
{
    use DBCreateTrait, DBReadTrait, DBUpdateTrait;

    /**
     * @SWG\Property(example=124)
     * @var int
     */
    public $id;

    /**
     * @SWG\Property(example=false)
     * @var bool
     */
    public $success = false;

    /**
     * @SWG\Property(example="2016-01-07T02:32:51Z", type="string")
     * @var LocalDateTime
     */
    public $updatedDate;

    /**
     * @SWG\Property(example="2008-12-24T03:16:00Z", type="string")
     * @var LocalDateTime
     */
    public $createdDate;

    /**
     * Returns a valid Avro 1.8.1 schema structure.
     *
     * @return array
     */
    public function getSchema()
    {
        return
            [
                "name" => "CheckoutRequest",
                "type" => "record",
                "fields" => [
                    ["name" => "id", "type" => "int"],
                    ["name" => "cancelRequestId", "type" => "int"],
                    ["name" => "patronBarcode", "type" => "string"],
                    ["name" => "itemBarcode", "type" => "string"],
                    ["name" => "owningInstitutionId", "type" => ["string", "null"]],
                    ["name" => "desiredDateDue", "type" => ["string", "null"]],
                    ["name" => "jobId", "type" => ["string", "null"]],
                    ["name" => "success", "type" => "boolean"],
                    ["name" => "createdDate", "type" => ["string", "null"]],
                    ["name" => "updatedDate", "type" => ["string", "null"]],
                ]
            ];
    }

    /**
     * @return string
     */
    public function getSequenceId()
    {
        return 'checkout_request_id_seq';
    }

    /**
     * @return array
     */
    public function getIdFields()
    {
        return ['id'];
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = (int)$id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return boolean
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @param boolean $success
     */
    public function setSuccess($success)
    {
        $this->success = (bool) $success;
    }

    /**
     * @return LocalDateTime
     */
    public function getUpdatedDate()
    {
        return $this->updatedDate;
    }

    /**
     * @param LocalDateTime $updatedDate
     */
    public function setUpdatedDate(LocalDateTime $updatedDate)
    {
        $this->updatedDate = $updatedDate;
    }

    /**
     * @param string $updatedDate
     *
     * @return LocalDateTime
     */
    public function translateUpdatedDate($updatedDate = '')
    {
        return new LocalDateTime(LocalDateTime::FORMAT_DATE_TIME_RFC, $updatedDate);
    }

    /**
     * @return LocalDateTime
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * @param LocalDateTime $createdDate
     */
    public function setCreatedDate(LocalDateTime $createdDate)
    {
        $this->createdDate = $createdDate;
    }

    /**
     * @param string $createdDate
     *
     * @return LocalDateTime
     */
    public function translateCreatedDate($createdDate = '')
    {
        return new LocalDateTime(LocalDateTime::FORMAT_DATE_TIME_RFC, $createdDate);
    }
}
