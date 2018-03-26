<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type\ContactData;

use Application\DeskPRO\Entity\ContactDataAbstract;
use Orb\Util\Strings;
use Symfony\Component\Form\FormEvent;

/**
 * Class LinkedInType.
 */
class LinkedInType extends AbstractUrlProfileType
{
    /**
     * {@inheritdoc}
     */
    public static function getContactType()
    {
        return ContactDataAbstract::TYPE_LINKED_IN;
    }

    /**
     * {@inheritdoc}
     */
    public function onParseProfilePath(FormEvent $event)
    {
        $data = $event->getData();

        $data->setField2(Strings::extractRegexMatch('#/in/(.*?)$#', $data->getField1(), 1));
    }
}
