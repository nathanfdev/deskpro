<?php

namespace DeskPRO\Bundle\ReportBundle\Form\Type\ScheduleTime;

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
