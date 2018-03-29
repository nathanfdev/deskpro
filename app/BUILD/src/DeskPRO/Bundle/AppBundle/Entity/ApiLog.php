<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\ApiKey;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ApiLog.
 *
 * @ORM\Entity()
 * @ORM\Table("api_log", uniqueConstraints={@ORM\UniqueConstraint(name="request_id_unique",columns={"request_id"})})
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 * @ORM\InheritanceType("NONE")
 *
 * @JMS\ExclusionPolicy("all")
 */
class ApiLog implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * The unique log id.
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     * @JMS\Groups({"list"})
     *
     * @var int
     */
    protected $id;

    /**
     * Timestamp when request was started.
     *
     * @ORM\Column(type="integer")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     * @JMS\Groups({"list"})
     *
     * @var int timestamp
     */
    protected $start_time;

    /**
     * Timestamp when request was ended.
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     * @JMS\Groups({"list"})
     *
     * @var int timestamp
     */
    protected $end_time;

    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\ApiKey", inversedBy="api_logs")
     * @ORM\JoinColumn(name="api_key_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var ApiKey
     */
    protected $key;

    /**
     * Credentials with which request was made.
     *
     * @ORM\Column(type="string", nullable=false)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups({"list"})
     *
     * @var string
     */
    protected $credentials;

    /**
     * Request api mode.
     *
     * @ORM\Column(type="string", nullable=false)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups({"list"})
     *
     * @var string
     */
    protected $mode;

    /**
     * Uri that was requested.
     *
     * @ORM\Column(type="string", nullable=false)
     *
     * @Assert\NotNull()
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups({"list"})
     *
     * @var string
     */
    protected $requested_uri;

    /**
     * Request method.
     *
     * @ORM\Column(type="string", nullable=false)
     *
     * @Assert\NotNull()
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups({"list"})
     *
     * @var string
     */
    protected $method;

    /**
     * HTTP status.
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     * @JMS\Groups({"list"})
     *
     * @var string
     */
    protected $status;

    /**
     * Data which was attached to request.
     *
     *
     * @ORM\Column(type="json_array")
     *
     * @JMS\Expose()
     * @JMS\Type("array")
     * @JMS\Groups({"details"})
     *
     * @var array
     */
    protected $request_data;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     *
     * @var array
     */
    protected $response_data;

    /**
     * Unique request identity (could be provided by client, see docs).
     *
     * @ORM\Column(type="string", nullable=false)
     *
     * @Assert\NotNull()
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups({"list"})
     *
     * @var string
     */
    protected $request_id;

    /**
     * Flag indicates that request is dupe.
     *
     * @ORM\Column(type="boolean", nullable=false, name="is_dupe")
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     * @JMS\Groups({"list"})
     *
     * @var bool
     */
    private $isDupe = false;

    /**
     * @ORM\Column(type="boolean", nullable=false, name="is_request_truncated")
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     * @JMS\Groups({"details"})
     *
     * @var bool
     */
    private $isRequestTruncated = false;

    /**
     * @ORM\Column(type="boolean", nullable=false, name="is_response_truncated")
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     * @JMS\Groups({"details"})
     *
     * @var bool
     */
    private $isResponseTruncated = false;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->setModelField('id', $id);

        return $this;
    }

    /**
     * @return int
     */
    public function getStartTime()
    {
        return $this->start_time;
    }

    /**
     * @param int $start_time
     *
     * @return $this
     */
    public function setStartTime($start_time)
    {
        $this->setModelField('start_time', $start_time);

        return $this;
    }

    /**
     * @return int
     */
    public function getEndTime()
    {
        return $this->end_time;
    }

    /**
     * @param int $end_time
     *
     * @return $this
     */
    public function setEndTime($end_time)
    {
        $this->setModelField('end_time', $end_time);

        return $this;
    }

    /**
     * @return ApiKey
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param ApiKey $key
     *
     * @return $this
     */
    public function setKey(ApiKey $key = null)
    {
        $this->setModelField('key', $key);

        return $this;
    }

    /**
     * @return string
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * @param string $credentials
     *
     * @return $this
     */
    public function setCredentials($credentials)
    {
        $this->setModelField('credentials', $credentials);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param mixed $mode
     *
     * @return $this
     */
    public function setMode($mode)
    {
        $this->setModelField('mode', $mode);

        return $this;
    }

    /**
     * @return string
     */
    public function getRequestedUri()
    {
        return $this->requested_uri;
    }

    /**
     * @param string $requested_uri
     *
     * @return $this
     */
    public function setRequestedUri($requested_uri)
    {
        $this->setModelField('requested_uri', $requested_uri);

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     *
     * @return $this
     */
    public function setMethod($method)
    {
        $this->setModelField('method', strtoupper($method));

        return $this;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->setModelField('status', $status);

        return $this;
    }

    /**
     * @return string
     */
    public function getRequestData($key = null)
    {
        if ($key && isset($this->request_data[$key])) {
            return $this->request_data[$key];
        } elseif ($key) {
            throw new \InvalidArgumentException(sprintf('Request [ %s ] key was not found in request_data property', $key));
        }

        return $this->request_data;
    }

    /**
     * @param array $request_data
     *
     * @return $this
     */
    public function setRequestData(array $request_data)
    {
        $this->setModelField('request_data', $request_data);

        return $this;
    }

    /**
     * @return array
     */
    public function getResponseData()
    {
        return $this->response_data;
    }

    /**
     * Get already decoded response data as array.
     *
     * @JMS\VirtualProperty()
     * @JMS\Groups({"details"})
     * @JMS\Type("array")
     * @JMS\SerializedName("response_data")
     */
    public function getDecodedResponseData()
    {
        $response_data         = $this->response_data;
        $response_data['body'] = $this->isResponseTruncated()
            ? $response_data['body']
            : json_decode($response_data['body'], true);

        return $response_data;
    }

    /**
     * @param $request_id
     *
     * @return $this
     */
    public function setRequestId($request_id)
    {
        $this->setModelField('request_id', $request_id);

        return $this;
    }

    /**
     * @return string
     */
    public function getRequestId()
    {
        return $this->request_id;
    }

    /**
     * @param array $response_data
     *
     * @return $this
     */
    public function setResponseData(array $response_data)
    {
        $this->setModelField('response_data', $response_data);

        return $this;
    }

    /**
     * @return bool
     */
    public function isDupe()
    {
        return $this->isDupe;
    }

    /**
     * @param bool $isDupe
     *
     * @return $this
     */
    public function setIsDupe($isDupe)
    {
        $this->setModelField('isDupe', (bool) $isDupe);

        return $this;
    }

    /**
     * @return bool
     */
    public function isRequestTruncated()
    {
        return $this->isRequestTruncated;
    }

    /**
     * @param bool $isRequestTruncated
     *
     * @return $this
     */
    public function setIsRequestTruncated($isRequestTruncated)
    {
        $this->setModelField('isRequestTruncated', (bool) $isRequestTruncated);

        return $this;
    }

    /**
     * @return bool
     */
    public function isResponseTruncated()
    {
        return $this->isResponseTruncated;
    }

    /**
     * @param bool $isResponseTruncated
     *
     * @return $this
     */
    public function setIsResponseTruncated($isResponseTruncated)
    {
        $this->setModelField('isResponseTruncated', (bool) $isResponseTruncated);

        return $this;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return [
            'id',
            'start_time',
            'end_time',
            'status',
            'response_data',
            'request_data',
            'requested_uri',
            'request_id',
        ];
    }
}
