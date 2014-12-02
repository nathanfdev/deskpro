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
 * @subpackage
 */

namespace Application\FormBundle\Form;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\CustomDefTicket;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * A service responsible for making sense of "Fields". Usually, special strings (see FormFields class), need to be
 * expanded into more information or fetched from the database.
 */
class FormFieldManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getCustomTicketField(CustomDefTicket $field, $agent_interface)
    {
        return $this->createCustomField($field, $agent_interface);
    }

    public function getCustomPersonField(CustomDefPerson $field, $agent_interface)
    {
        return $this->createCustomField($field, $agent_interface);
    }

    public function getCustomTicketFieldById($id, $agent_interface)
    {
        return $this->em->getRepository('DeskPRO:CustomDefTicket')->find($id);
    }

    public function getCustomPersonFieldById($id)
    {
        return $this->em->getRepository('DeskPRO:CustomDefPerson')->find($id);
    }

    /**
     * @param CustomDefPerson $field
     * @return array
     */
    protected function createCustomField(CustomDefAbstract $field, $agent_interface)
    {
        list($type, $value_name, $options) = $this->getFormType($field, $agent_interface);
        if (!array_key_exists('label', $options)) {
            $options['label'] = $field->getTitle();
        }
        $options['help'] = $field->getDescription();

        return array($value_name, $type, $options);
    }

    private function getFormType(CustomDefAbstract $field_type, $agent_interface)
    {
        switch ($field_type->getHandlerClass()) {
            case 'Application\\DeskPRO\\CustomFields\\Handler\\Text':

                return array(
                    'text',
                    'input',
                    $this->getGeneralOptionsForField($field_type, array()));

            case 'Application\\DeskPRO\\CustomFields\\Handler\\Textarea':

                return array(
                    'textarea',
                    'input',
                    $this->getGeneralOptionsForField($field_type, array()));

            case 'Application\\DeskPRO\\CustomFields\\Handler\\Toggle':

                return array(
                    'checkbox',
                    'value',
                    $this->getGeneralOptionsForField($field_type, array(
                        'checkbox_label' => $field_type->getOption('label_text'),
                        'force_boolean'  => true
                    )));

            case 'Application\\DeskPRO\\CustomFields\\Handler\\Display':

                return array(
                    'deskpro_display_html',
                    'input',
                    $this->getGeneralOptionsForField($field_type, array(
                        'html' => $field_type->getOption('html'),
                        'data' => ''
                    )));

            case 'Application\\DeskPRO\\CustomFields\\Handler\\Choice':

                $multiple = (bool)$field_type->getOption('multiple');
                $expanded = (bool)$field_type->getOption('expanded');

                return array(
                    'deskpro_custom_field_choice',
                    $multiple ? 'input' : 'value',
                    $this->getGeneralOptionsForField($field_type, array(
                        'expanded'     => $expanded,
                        'multiple'     => $multiple,
                        'custom_field' => $field_type
                    )));

            case 'Application\\DeskPRO\\CustomFields\\Handler\\Hidden':

                $options = array(
                    'auto_fill'          => !$agent_interface,
                    'hidden'             => !$agent_interface,
                    'cookie_param_name'  => $field_type->getOption('cookie_name'),
                    'request_param_name' => $field_type->getOption('param_name')
                );

                if (!$agent_interface) {
                    $options['label'] = false;
                }

                return array('deskpro_hidden', 'input', $this->getGeneralOptionsForField($field_type, $options));

            default:
                break;
        }

        throw new \InvalidArgumentException('invalid field. cannot find type for handler class: '.$field_type->getHandlerClass());
    }

    private function getGeneralOptionsForField(CustomDefAbstract $field_type, array $specific_options)
    {
        $isAgent = false;

        $options = array(
            'required' => $field_type->isRequired($isAgent)
        );

        $constraints = array();
        if ($field_type->isRequired($isAgent)) {
            $constraints[] = new NotBlank(array('message' => 'This value is required'));
        }

        $min = $field_type->getMinLength($isAgent);
        $max = $field_type->getMaxLength($isAgent);
        if ($min || $max) {
            $opts = array();

            if ($min) {
                $opts['min'] = $min;
            }
            if ($max) {
                $opts['max'] = $max;
            }

            $constraints[] = new Length($opts);
        }

        $options['constraints'] = $constraints;

        return array_merge($options, $specific_options);
    }
}
