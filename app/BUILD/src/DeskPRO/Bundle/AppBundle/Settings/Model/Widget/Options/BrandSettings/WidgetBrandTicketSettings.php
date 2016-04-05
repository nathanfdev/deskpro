<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @Assert\NotBlank()
     */
    private $selectDepartment = self::SELECT_DEFAULT;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @Assert\NotBlank(groups={"default_department"})
     */
    private $defaultDepartment;

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
     * {@inheritdoc}
     */
    public function getGroupSequence()
    {
        $groups = ['common'];
        if ($this->selectDepartment === self::SELECT_DEFAULT) {
            $groups[] = 'default_department';
        }

        return $groups;
    }
}
