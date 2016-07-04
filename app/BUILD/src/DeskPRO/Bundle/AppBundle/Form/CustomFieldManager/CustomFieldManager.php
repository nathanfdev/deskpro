<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Form\CustomFieldManager;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomDefChat;
use Application\DeskPRO\Entity\CustomDefFeedback;
use Application\DeskPRO\Entity\CustomDefOrganization;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\Form\FormField;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFieldChoiceType;
use DeskPRO\Bundle\AppBundle\Form\Type\DateTimeType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A service responsible for making sense of "Fields". Usually, special strings (see FormFields class), need to be
 * expanded into more information or fetched from the database.
 */
class CustomFieldManager
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
            $constraints[] = new Assert\NotBlank();
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
    public function getAvailablePersonDefs()
    {
        return $this->getAvailableCustomDefs(CustomDefPerson::class);
    }

    /**
     * @return CustomDefFeedback[]
     */
    public function getAvailableFeedbackDefs()
    {
        return $this->getAvailableCustomDefs(CustomDefFeedback::class);
    }

    /**
     * @return CustomDefOrganization[]
     */
    public function getAvailableOrganizationDefs()
    {
        return $this->getAvailableCustomDefs(CustomDefOrganization::class);
    }

    /**
     * @return CustomDefTicket[]
     */
    public function getAvailableTicketDefs()
    {
        return $this->getAvailableCustomDefs(CustomDefTicket::class);
    }

    /**
     * @return CustomDefChat[]
     */
    public function getAvailableChatDefs()
    {
        return $this->getAvailableCustomDefs(CustomDefChat::class);
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
                return false;
        }
    }

    /**
     * @param $id
     *
     * @return CustomDefTicket
     */
    public function getCustomTicketFieldById($id)
    {
        return $this->em->getRepository(CustomDefTicket::class)->find($id);
    }

    /**
     * @param $id
     *
     * @return CustomDefPerson
     */
    public function getCustomPersonFieldById($id)
    {
        return $this->em->getRepository(CustomDefPerson::class)->find($id);
    }

    /**
     * @param $id
     *
     * @return CustomDefOrganization
     */
    public function getCustomOrganizationFieldById($id)
    {
        return $this->em->getRepository(CustomDefOrganization::class)->find($id);
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
        return $this->em->getRepository(CustomFieldDefinition::class)->find($id);
    }

    /**
     * @param CustomDefAbstract $def
     * @param bool              $is_inline
     *
     * @return FormField
     */
    public function createCustomField(CustomDefAbstract $def, $is_inline = false)
    {
        switch ($def->getType()) {
            case CustomDefAbstract::TYPE_TEXT:
                return new FormField(TextType::class, $this->getGeneralOptionsForField($def, []));
            case CustomDefAbstract::TYPE_TEXTAREA:
                return new FormField(TextareaType::class, $this->getGeneralOptionsForField($def, []));
            case CustomDefAbstract::TYPE_TOGGLE:
                $options = [
                    'checkbox_label' => $def->getOption('label_text') ?: '',
                    'force_boolean'  => true,
                ];

                return new FormField(
                    'single_checkbox',
                    $this->getGeneralOptionsForField($def, $options)
                );

            case CustomDefAbstract::TYPE_DISPLAY:
                $options = [
                    'html'  => $def->getOption('html'),
                    'data'  => '',
                    'label' => false,
                ];

                return new FormField(
                    'deskpro_display_html',
                    $this->getGeneralOptionsForField($def, $options)
                );

            case CustomDefAbstract::TYPE_CHOICE:
                $options = [
                    'expanded'     => (bool) $def->getOption('expanded'),
                    'multiple'     => (bool) $def->getOption('multiple'),
                    'custom_field' => $def,
                ];

                return new FormField(
                    CustomFieldChoiceType::class,
                    $this->getGeneralOptionsForField($def, $options)
                );

            case CustomDefAbstract::TYPE_DATE:
                if ($is_inline) {
                    $options = [
                        'input'  => 'timestamp',
                        'widget' => 'single_text',
                    ];
                } else {
                    $options = [
                        'input'    => 'timestamp',
                        'widget'   => 'choice',
                        'weekdays' => $def->getOption('date_valid_dow'),
                        'min_date' => $def->getDateMinFormat(),
                        'max_date' => $def->getDateMaxFormat(),
                    ];
                }

                return new FormField(
                    'deskpro_date',
                    $this->getGeneralOptionsForField($def, $options)
                );

            case CustomDefAbstract::TYPE_DATETIME:
                if ($is_inline) {
                    $options = [
                        'input'  => 'timestamp',
                        'widget' => 'single_text',
                    ];

                    return new FormField(
                        'datetime',
                        $this->getGeneralOptionsForField($def, $options)
                    );
                } else {
                    $options = [
                        'input'    => 'timestamp',
                        'widget'   => 'choice',
                        'format'   => 'Y-m-d H:i',
                        'weekdays' => $def->getOption('date_valid_dow'),
                        'min_date' => $def->getDateMinFormat(),
                        'max_date' => $def->getDateMaxFormat(),
                    ];

                    return new FormField(
                        DateTimeType::class,
                        $this->getGeneralOptionsForField($def, $options)
                    );
                }

            case CustomDefAbstract::TYPE_HIDDEN:
                $options = [
                    'auto_fill'          => false,
                    'hidden'             => true,
                    'label'              => false,
                    'help'               => false,
                    'cookie_param_name'  => $def->getOption('cookie_name'),
                    'request_param_name' => $def->getOption('param_name'),
                ];

                return new FormField(
                    'deskpro_hidden',
                    $this->getGeneralOptionsForField($def, $options)
                );

            default:
                throw new \InvalidArgumentException("Invalid field #{$def->getId()}. Cannot find handler for type \"{$def->getType()}\".");
        }
    }

    /**
     * @param string $entityType
     *
     * @return CustomDefAbstract[]
     */
    private function getAvailableCustomDefs($entityType)
    {
        $qb = $this->em
            ->createQueryBuilder()
            ->select('f')
            ->from($entityType, 'f')
            ->where(
                'f.is_user_enabled = true',
                'f.is_enabled = true',
                'f.handler_class IS NOT NULL'
            )
            ->orderBy('f.display_order')
        ;

        $result = new ArrayCollection($qb->getQuery()->getResult());
        $result = $result->filter(function (CustomDefAbstract $def) {
            if ($def->isChoiceType() && !$def->hasChildren()) {
                return false;
            }

            return true;
        });

        return $result;
    }

    /**
     * @param CustomDefAbstract $field_type
     * @param array             $specific_options
     *
     * @return array
     */
    private function getGeneralOptionsForField(CustomDefAbstract $field_type, array $specific_options)
    {
        $options['help'] = $field_type->getRealDescription();

        return array_merge($options, $specific_options);
    }
}
