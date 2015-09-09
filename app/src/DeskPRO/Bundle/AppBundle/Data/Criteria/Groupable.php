<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\Data\Criteria;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class Groupable
 */
trait Groupable
{
    /**
     * @var string
     */
    private $group_by;

    /**
     * Get group_by
     * @return string
     */
    public function getGroupBy()
    {
        return $this->group_by;
    }

    /**
     * Set group_by
     * @param string $value
     */
    public function setGroupBy($value)
    {
        $this->group_by = $value;
    }

    /**
     * If group_by is set
     * @return bool
     */
    public function hasGroupBy()
    {
        return (bool) $this->group_by;
    }

    /**
     * Ensures group_by is set
     * @throws \LogicException
     */
    public function ensureGroupBy()
    {
        if (!$this->hasGroupBy()) {
            throw new \LogicException('Cannot group without group_by');
        }
    }

    /**
     * Process and remove group_by from given parameters
     *
     * @param array $params
     * @return string
     */
    public static function extractGroupBy(array &$params)
    {
        $group_by = null;
        if (array_key_exists('group_by', $params)) {
            $group_by = $params['group_by'];
            unset($params['group_by']);
        }

        return $group_by;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public static function configureGroupByResolver(OptionsResolver $resolver)
    {
        $resolver->setDefined(array_merge($resolver->getDefinedOptions(), ['group_by']));
        $resolver->setAllowedValues('group_by', (new self())->getGroupByAllowedValues());
    }

    /**
     * @return array
     */
    public abstract function getGroupByAllowedValues();
}