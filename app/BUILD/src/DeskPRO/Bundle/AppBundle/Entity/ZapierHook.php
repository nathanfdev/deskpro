<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Person;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ZapierHook.
 *
 * @ORM\Entity()
 * @ORM\Table(name="zapier_hooks")
 *
 * @JMS\ExclusionPolicy("all")
 */
class ZapierHook implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="target_url", type="string", length=255, nullable=true)
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $targetUrl;

    /**
     * @ORM\Column(name="event", type="string", length=255, nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    private $event;

    /**
     * @ORM\Column(type="json_array", name="params", nullable=true)
     *
     * @var array
     */
    private $params = [];

    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     *
     * @Assert\NotBlank()
     *
     * @var Person
     */
    private $person;

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
     * @return ZapierHook
     */
    public function setId($id)
    {
        $this->setModelField('id', $id);

        return $this;
    }

    /**
     * @return string
     */
    public function getTargetUrl()
    {
        return $this->targetUrl;
    }

    /**
     * @param string $targetUrl
     *
     * @return ZapierHook
     */
    public function setTargetUrl($targetUrl)
    {
        $this->setModelField('targetUrl', $targetUrl);

        return $this;
    }

    /**
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param string $event
     *
     * @return ZapierHook
     */
    public function setEvent($event)
    {
        $this->setModelField('event', $event);

        return $this;
    }

    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param array $params
     *
     * @return ZapierHook
     */
    public function setParams($params)
    {
        $params = array_map('strval', $params);
        $this->setModelField('params', $params);

        return $this;
    }

    public function addParam($param, $key = '')
    {
        $this->params[$key] = $param;
        $this->setModelField('params', $this->params);

        return $this;
    }

    public function hasParam($param)
    {
        return in_array($param, $this->params, true);
    }

    public function removeParam($param)
    {
        if (false !== $key = array_search($param, $this->params, true)) {
            unset($this->params[$key]);
            $this->params = array_values($this->params);
        }
        $this->setModelField('params', $this->params);

        return $this;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param Person $person
     *
     * @return ZapierHook
     */
    public function setPerson($person)
    {
        $this->setModelField('person', $person);

        return $this;
    }
}
