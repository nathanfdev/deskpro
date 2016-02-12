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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\Form\Form;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomDefFeedback;
use Application\DeskPRO\Entity\CustomDefOrganization;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\DpDate;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\ValidRegex;
use Doctrine\ORM\EntityManager;
use Orb\Util\Strings;
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

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param CustomFieldDefinition $field
     * @param bool                  $agent_interface
     *
     * @return array
     */
    public function getCustomPerField(CustomFieldDefinition $field, $agent_interface)
    {
        $constraints = [];

        // required
        if ($field->isRequired($agent_interface)) {
            $constraints[] = new NotBlank(['message' => 'This value is required']);
        }

        $options = [
            'required'     => $field->isRequired($agent_interface),
            'expanded'     => $field->isExpanded(),
            'multiple'     => $field->isMultiple(),
            'custom_field' => $field,
            'label'        => false,
            'constraints'  => $constraints,
            'help'         => $field->getDescription(),
        ];

        return [
            'data',
            'deskpro_contextual_per_field_choice',
            $options,
        ];
    }

    /**
     * @return CustomDefPerson[]
     */
    public function getAvailablePersonFields()
    {
        return $this->em
            ->createQueryBuilder()
            ->select('f')
            ->from('DeskPRO:CustomDefPerson', 'f')
            ->where(
                'f.is_user_enabled = true',
                'f.is_enabled = true',
                'f.handler_class IS NOT NULL'
            )
            ->orderBy('f.display_order')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return CustomDefFeedback[]
     */
    public function getFeedbackFields()
    {
        $fields     = [];
        $all_fields = $this->em->getRepository('DeskPRO:CustomDefFeedback')->findAll();

        /** @var CustomDefAbstract $field */
        foreach ($all_fields as $field) {
            if (!$field->isEnabled()) {
                continue;
            }

            if ($field->getParent()) {
                continue;
            }

            $fields[] = $field;
        }

        return $fields;
    }

    /**
     * @param LayoutField $layout_field
     *
     * @return CustomDefAbstract|null
     */
    public function getCustomDefForLayoutField(LayoutField $layout_field)
    {
        $field_id = $layout_field->getFieldId();
        switch ($layout_field->getFieldType()) {
            case FormFields::TICKET_FIELD:
                return $this->getCustomTicketFieldById($field_id);
            case FormFields::USER_FIELD:
                return $this->getCustomTicketFieldById($field_id);
            case FormFields::ORG_FIELD:
                return $this->getCustomTicketFieldById($field_id);
            default:
                return;
        }
    }

    /**
     * @param $id
     *
     * @return CustomDefTicket
     */
    public function getCustomTicketFieldById($id)
    {
        return $this->em->getRepository('DeskPRO:CustomDefTicket')->find($id);
    }

    /**
     * @param $id
     *
     * @return CustomDefPerson
     */
    public function getCustomPersonFieldById($id)
    {
        return $this->em->getRepository('DeskPRO:CustomDefPerson')->find($id);
    }

    /**
     * @param $id
     *
     * @return CustomDefOrganization
     */
    public function getCustomOrganizationFieldById($id)
    {
        return $this->em->getRepository('DeskPRO:CustomDefOrganization')->find($id);
    }

    /**
     * per-user/per-organization special custom fields.
     *
     * @param $id
     *
     * @return \Application\DeskPRO\Entity\CustomFieldDefinition
     */
    public function getCustomPerFieldById($id)
    {
        return $this->em->getRepository('DeskPRO:CustomFieldDefinition')->find($id);
    }

    /**
     * @param CustomDefAbstract $field
     * @param bool              $is_agent
     * @param bool              $is_inline
     *
     * @return array
     */
    public function createCustomField(CustomDefAbstract $field, $is_agent = false, $is_inline = false)
    {
        list($type, $value_name, $options) = $this->getFormType($field, $is_agent, $is_inline);

        // custom fields are implemented as a compound type
        // and this label is for the 'data' attribute, whereas
        // the real label will be on the parent form which is adding the field
        $options['label'] = false;
        $options['help']  = $field->getDescription();

        return [$value_name, $type, $options];
    }

    /**
     * @param CustomDefAbstract $field_type
     * @param bool              $is_agent
     * @param bool              $is_inline
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    private function getFormType(CustomDefAbstract $field_type, $is_agent, $is_inline)
    {
        switch ($field_type->getHandlerClass()) {
            case 'Application\\DeskPRO\\CustomFields\\Handler\\Text':
                return [
                    'text',
                    'input',
                    $this->getGeneralOptionsForField($field_type, [], $is_agent),
                ];

            case 'Application\\DeskPRO\\CustomFields\\Handler\\Textarea':
                return [
                    'textarea',
                    'input',
                    $this->getGeneralOptionsForField($field_type, [], $is_agent),
                ];

            case 'Application\\DeskPRO\\CustomFields\\Handler\\Toggle':
                return [
                    'single_checkbox',
                    'value',
                    $this->getGeneralOptionsForField($field_type, [
                        'checkbox_label' => $field_type->getOption('label_text') ?: '',
                        'force_boolean'  => true,
                    ], $is_agent),
                ];

            case 'Application\\DeskPRO\\CustomFields\\Handler\\Display':
                return [
                    'deskpro_display_html',
                    'input',
                    $this->getGeneralOptionsForField($field_type, [
                        'html'  => $field_type->getOption('html'),
                        'data'  => '',
                        'label' => false,
                    ], $is_agent),
                ];

            case 'Application\\DeskPRO\\CustomFields\\Handler\\Choice':
                $multiple = (bool) $field_type->getOption('multiple');
                $expanded = (bool) $field_type->getOption('expanded');

                return [
                    'deskpro_custom_field_choice',
                    'data',
                    $this->getGeneralOptionsForField($field_type, [
                        'expanded'     => $expanded,
                        'multiple'     => $multiple,
                        'custom_field' => $field_type,
                    ], $is_agent),
                ];

            case 'Application\\DeskPRO\\CustomFields\\Handler\\Date':
                return [
                    'deskpro_date',
                    'input',
                    $this->getGeneralOptionsForField($field_type, [
                        'input'  => 'string',
                        'widget' => 'choice',
                        'format' => 'y-M-d',
                    ], $is_agent),
                ];

            case 'Application\\DeskPRO\\CustomFields\\Handler\\DateTime':
                $options = $this->getGeneralOptionsForField($field_type, [
                    'input'  => 'string',
                    'widget' => 'choice',
                    'format' => 'Y-m-d H:i',
                ], $is_agent);

                return [
                    'deskpro_datetime',
                    'input',
                    $options,
                ];

            case 'Application\\DeskPRO\\CustomFields\\Handler\\Hidden':
                $options = [
                    'auto_fill'          => false,
                    'hidden'             => true,
                    'label'              => false,
                    'help'               => false,
                    'cookie_param_name'  => $field_type->getOption('cookie_name'),
                    'request_param_name' => $field_type->getOption('param_name'),
                ];

                return ['deskpro_hidden', 'input', $this->getGeneralOptionsForField($field_type, $options, $is_agent)];

            default:
                break;
        }

        throw new \InvalidArgumentException('invalid field. cannot find type for handler class: '.$field_type->getHandlerClass());
    }

    /**
     * @param CustomDefAbstract $field_type
     * @param array             $specific_options
     * @param bool              $is_agent
     *
     * @return array
     */
    private function getGeneralOptionsForField(CustomDefAbstract $field_type, array $specific_options, $is_agent)
    {
        $constraints = $options = [];

        // required
        if ($field_type->isRequired($is_agent)) {
            $options['required'] = $field_type->isRequired($is_agent);
            $constraints[]       = new NotBlank(['message' => 'portal.forms.error_required']);
        }

        // length
        $min = $field_type->getMinLength($is_agent);
        $max = $field_type->getMaxLength($is_agent);
        if ($min || $max) {
            $opts = [];

            if ($min) {
                $opts['min']        = $min;
                $opts['minMessage'] = 'portal.forms.error_length_min';
            }
            if ($max) {
                $opts['max']        = $max;
                $opts['maxMessage'] = 'portal.forms.error_length_max';
            }

            $constraints[] = new Length($opts);
        }

        $options['help'] = $field_type->getRealDescription();

        // regex
        $regex = $field_type->getRegex($is_agent);
        if ($regex) {
            $constraints[] = new ValidRegex([
                'pattern' => Strings::getInputRegexPattern($regex),
                'message' => 'portal.forms.error_regex',
            ]);
        }

        // date stuff
        if ($field_type->isDateType()) {
            $weekdays = $field_type->getValidWeekDays();
            if (!$weekdays || count($weekdays) === 0) {
                $weekdays = [0, 1, 2, 3, 4, 5, 6];
            }
            $min_date = $field_type->getDateMin();
            if (null !== $min_date) {
                try {
                    $min_date = $min_date instanceof \DateTime ? $min_date : new \DateTime(sprintf('now -%s days', $min_date));
                    $min_date->setTime(0, 0, 0);
                } catch (\Exception $e) {
                    $min_date = null;
                }
            }
            $max_date = $field_type->getDateMax();
            if (null !== $max_date) {
                try {
                    $max_date = $max_date instanceof \DateTime ? $max_date : new \DateTime(sprintf('now +%s days', $max_date));
                    $max_date->setTime(23, 59, 59);
                } catch (\Exception $e) {
                    $max_date = null;
                }
            }

            if ($weekdays || $min_date || $max_date) {
                $constraints[] = new DpDate(
                    [
                        'days_of_week' => $weekdays,
                        'min_date'     => $min_date ?: null,
                        'max_date'     => $max_date ?: null,
                    ]
                );
            }
        }

        $options['constraints'] = $constraints;

        return array_merge($options, $specific_options);
    }
}
