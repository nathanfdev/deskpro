<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\CustomFields;

use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Usersource;
use Orb\Auth\Identity;
use Orb\Util\Arrays;
use Orb\Util\Strings;

class PersonFieldManager extends FieldManager
{
    /**
     * Get an array of all defined fields (by doing a query).
     *
     * @return CustomDefPerson[]
     */
    public function getDefinedFields()
    {
        return array_values($this->em->getRepository('DeskPRO:CustomDefPerson')->getTopFields());
    }

    /**
     * @param string $id
     * @param bool   $enabled
     */
    public function setFieldEnabledById($id, $enabled = true)
    {
        if ($custom_field_id = Strings::extractRegexMatch('#^field_(\d+)$#', $id)) {
            $field             = $this->em->find('DeskPRO:CustomDefPerson', $custom_field_id);
            $field->is_enabled = $enabled;

            $this->em->persist($field);
            $this->em->flush($field);
        }
    }

    public function copyUsersourceData(Person $person, $raw_data, Usersource $usersource)
    {
        $save_data = [];

        foreach ($this->getDefinedFields() as $field) {
            if ($field->handler_class != 'Application\\DeskPRO\\CustomFields\\Handler\\Data') {
                continue;
            }

            if ($field->getOption('usersource_id') && $field->getOption('usersource_id') != $usersource->getId()) {
                continue;
            }

            $field_name = $field->getOption('field_name');

            if (!$field_name) {
                continue;
            }

            if ($raw_data instanceof Identity) {
                $raw_data = $raw_data->getRawData();
            }

            // Reads the value and does some common input error correction:
            // - Arrays are separated by a slash or a dot: telephonenumber.0 or telephonenumber/0
            // - If the key isnt found as-is, we'll also try converting to lowercase and trying again (keys are typically lowercase)
            $val = null;
            foreach (['/', '.'] as $sep) {
                $val = Arrays::keyAsPath($raw_data, $field_name, $sep, null);

                if ($val === null) {
                    $val = Arrays::keyAsPath($raw_data, strtolower($field_name), $sep, null);
                }

                if ($val !== null) {
                    break;
                }
            }

            if ($val === null) {
                continue;
            }

            // Automatically flatten arrays
            // E.g., LDAP will return attributes as an array, often with only one item
            // Without this we'd need to document that you need to specify attrName/0
            if (is_array($val)) {
                $val = implode("\n\n", $val);
            }

            $save_data['field_'.$field->getId()] = $val;
        }

        if ($save_data) {
            $this->saveFormToObject($save_data, $person, true);
        }
    }
}
