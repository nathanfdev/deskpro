<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\ContactDataAbstract;
use Application\DeskPRO\Entity\OrganizationContactData;
use Application\DeskPRO\Entity\PersonContactData;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use Orb\Util\Strings;

/**
 * Class ContactDataHandler.
 */
class ContactDataHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return [
            PersonContactData::class,
            OrganizationContactData::class,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @param ContactDataAbstract $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $contact_type = $entity->getContactType();
        $class_name   = 'DeskPRO\\Bundle\\AppBundle\\Serializer\\Model\\ContactData\\'.ucfirst(Strings::underscoreToCamelCase($contact_type));

        if (!class_exists($class_name)) {
            throw new \InvalidArgumentException("`$contact_type` is not a valid type");
        }

        return new $class_name($entity);
    }
}
