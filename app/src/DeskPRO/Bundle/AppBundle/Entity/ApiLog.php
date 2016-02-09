<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\ApiKey;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ApiLog.
 *
 * @ORM\Entity()
 * @ORM\Table("api_log", uniqueConstraints={@ORM\UniqueConstraint(name="request_id_unique",columns={"request_id"})})
 * @ORM\ChangeTrackingPolicy("DEFERRED_IMPLICIT")
 * @ORM\InheritanceType("NONE")
 */
class ApiLog implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var int timestamp
     * @ORM\Column(type="integer")
     */
    protected $start_time;

    /**
     * @var int timestamp
     * @ORM\Column(type="integer")
     */
    protected $end_time;

    /**
     * @var ApiKey
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\ApiKey", inversedBy="api_logs")
     * @ORM\JoinColumn(name="api_key_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $key;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Assert\NotNull()
     */
    protected $requested_uri;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $status;

    /**
     * @var array
     * @ORM\Column(type="json_array")
     */
    protected $request_data;

    /**
     * @var array
     * @ORM\Column(type="json_array")
     */
    protected $response_data;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Assert\NotNull()
     */
    protected $request_id;

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
        $this->id = $id;

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
        $this->start_time = $start_time;

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
        $this->end_time = $end_time;

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
    public function setKey(ApiKey $key)
    {
        $this->key = $key;

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
        $this->requested_uri = $requested_uri;

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
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getRequestData()
    {
        return $this->request_data;
    }

    /**
     * @param array $request_data
     *
     * @return $this
     */
    public function setRequestData(array $request_data)
    {
        $this->request_data = $request_data;

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
     * @param $request_id
     *
     * @return $this
     */
    public function setRequestId($request_id)
    {
        $this->request_id = $request_id;

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
        $this->response_data = $response_data;

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
