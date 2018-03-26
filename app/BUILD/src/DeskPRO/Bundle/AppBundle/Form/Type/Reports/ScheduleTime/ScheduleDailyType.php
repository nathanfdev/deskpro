<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Reports\ScheduleTime;

use Symfony\Component\Form\AbstractType;

/**
 * Class ScheduleDailyType.
 */
class ScheduleDailyType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ScheduleWhenType::class;
    }
}
