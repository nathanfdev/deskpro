<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type\ContactData;

use Application\DeskPRO\Entity\ContactDataAbstract;
use DeskPRO\Component\Util\RegexUtils;
use Symfony\Component\Form\FormEvent;

/**
 * Class FacebookType.
 */
class FacebookType extends AbstractUrlProfileType
{
    /**
     * {@inheritdoc}
     */
    public static function getContactType()
    {
        return ContactDataAbstract::TYPE_FACEBOOK;
    }

    /**
     * {@inheritdoc}
     */
    public function onParseProfilePath(FormEvent $event)
    {
        $data = $event->getData();

        if (RegexUtils::safePregMatch('#/profile\.php?id=([0-9]+)#', $data->getField1(), $m)) {
            $data->setField2($m[1]);
        } elseif (RegexUtils::safePregMatch('#facebook\.com/([a-zA-Z0-9\.\-_]+)#', $data->getField1(), $m)) {
            $data->setField2($m[1]);
        } elseif (RegexUtils::safePregMatch('#facebook\.com/people/([a-zA-Z0-9\.\-_]+)#', $data->getField1(), $m)) {
            $data->setField2($m[1]);
        }
    }
}
