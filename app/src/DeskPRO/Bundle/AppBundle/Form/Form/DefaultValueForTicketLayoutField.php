<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Form\Form;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use Doctrine\ORM\EntityManager;

class DefaultValueForTicketLayoutField
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var FormFieldManager
     */
    private $field_manager;

    public function __construct(EntityManager $em, FormFieldManager $field_manager)
    {
        $this->em            = $em;
        $this->field_manager = $field_manager;
    }

    /**
     * Gets the default data structure you use in a form during a submit to apply the correct data to the fields
     * form type. This comes from a string in the database.
     *
     * If no default exists, return null.
     *
     * @param LayoutField $field
     *
     * @return array|null
     */
    public function determineDefaultSubmitData(LayoutField $field)
    {
        switch ($field->getFieldType()) {
            case FormFields::TICKET_FIELD:
                $def                                    = $this->field_manager->getCustomTicketFieldById($field->getFieldId());
                list($value_name, $form_type, $options) = $this->field_manager->getCustomTicketField($def, false);

                return $this->extractDefaultData($def, $value_name, $form_type, $options);
            case FormFields::ORG_FIELD:
                $def                                    = $this->field_manager->getCustomOrganizationFieldById($field->getFieldId());
                list($value_name, $form_type, $options) = $this->field_manager->getCustomOrganizationField($def, false);

                return $this->extractDefaultData($def, $value_name, $form_type, $options);
            case FormFields::USER_FIELD:
                $def                                    = $this->field_manager->getCustomPersonFieldById($field->getFieldId());
                list($value_name, $form_type, $options) = $this->field_manager->getCustomPersonField($def, false);

                return $this->extractDefaultData($def, $value_name, $form_type, $options);
        }

        return;
    }

    /**
     * Every form type handles their input differently. This method makes the correct array data structure from
     * the stored default string value.
     *
     * @param CustomDefAbstract $def
     * @param $value_name
     * @param $form_type
     * @param $options
     *
     * @return array|null
     */
    protected function extractDefaultData(CustomDefAbstract $def, $value_name, $form_type, $options)
    {
        $default_string = (string) $def->getDefaultValue();
        $default        = null;

        if (strlen(trim($default_string)) < 1) {
            return;
        }

        if (in_array($form_type, ['text', 'textarea', 'single_checkbox', 'deskpro_hidden'])) {
            $default = $default_string;
        } elseif (in_array($form_type, ['deskpro_custom_field_choice'])) {
            if ($options['multiple']) {
                $default = explode(',', $default_string);
            } else {
                $default = $default_string;
            }
        } elseif (in_array($form_type, ['deskpro_date', 'deskpro_datetime'])) {
            try {
                $date = new \DateTime($default_string);

                $default = [
                  'date' => [
                      'year'  => (string) $date->format('Y'),
                      'month' => (string) $date->format('n'),
                      'day'   => (string) $date->format('j'),
                  ],
                ];

                if ($form_type == 'deskpro_datetime') {
                    $default['time'] = [
                        'hour'   => (string) $date->format('G'),
                        'minute' => (string) ((int) $date->format('i')), // single digit minute
                    ];
                }
            } catch (\Exception $e) {
                return;
            }
        } else {
            return;
        }

        return [
            $value_name => $default,
        ];
    }
}
