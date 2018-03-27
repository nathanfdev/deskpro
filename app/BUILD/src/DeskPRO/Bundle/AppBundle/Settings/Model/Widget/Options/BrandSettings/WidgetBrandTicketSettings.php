<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

/**
 * Class WidgetTicketSettings.
 *
 * @Assert\GroupSequenceProvider
 */
class WidgetBrandTicketSettings implements GroupSequenceProviderInterface
{
    const SELECT_DEFAULT = 'default';
    const SELECT_CUSTOM  = 'custom';
    const SELECT_MESSAGE = 'message';

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @Assert\NotBlank(groups={"Common"})
     */
    private $selectDepartment = self::SELECT_CUSTOM;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @Assert\NotNull(groups={"DefaultDepartment"})
     */
    private $defaultDepartment;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $selectSubject = self::SELECT_CUSTOM;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $defaultSubject;

    /**
     * @return string
     */
    public function getSelectDepartment()
    {
        return $this->selectDepartment;
    }

    /**
     * @param string $selectDepartment
     *
     * @return $this
     */
    public function setSelectDepartment($selectDepartment)
    {
        $this->selectDepartment = $selectDepartment;

        return $this;
    }

    /**
     * @return int
     */
    public function getDefaultDepartment()
    {
        return $this->defaultDepartment;
    }

    /**
     * @param int $defaultDepartment
     *
     * @return $this
     */
    public function setDefaultDepartment($defaultDepartment)
    {
        $this->defaultDepartment = $defaultDepartment;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultSubject()
    {
        return $this->defaultSubject;
    }

    /**
     * @param string $defaultSubject
     */
    public function setDefaultSubject($defaultSubject)
    {
        $this->defaultSubject = $defaultSubject;
    }

    /**
     * @return string
     */
    public function getSelectSubject()
    {
        return $this->selectSubject;
    }

    /**
     * @param string $selectSubject
     */
    public function setSelectSubject($selectSubject)
    {
        $this->selectSubject = $selectSubject;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupSequence()
    {
        $groups = ['Common'];
        if ($this->selectDepartment === self::SELECT_DEFAULT) {
            $groups[] = 'DefaultDepartment';
        }

        return $groups;
    }
}
