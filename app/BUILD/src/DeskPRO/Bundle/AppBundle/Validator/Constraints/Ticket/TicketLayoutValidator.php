<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Ticket;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomDefOrganization;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyGenerator;
use DeskPRO\Bundle\AppBundle\Ticket\TicketFieldSettings;
use DeskPRO\Bundle\AppBundle\Ticket\TicketLayoutFactory;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class TicketLayoutValidator.
 */
class TicketLayoutValidator extends ConstraintValidator
{
    /**
     * @var CustomFieldManager
     */
    private $field_manager;

    /**
     * @var TicketLayoutFactory
     */
    private $ticket_layout_factory;

    /**
     * @var HierarchyGenerator
     */
    private $hierarchy_generator;

    /**
     * @var TicketFieldSettings
     */
    private $ticket_field_settings;

    /**
     * @var array
     */
    private $constraints = [];

    /**
     * Constructor.
     *
     * @param CustomFieldManager  $field_manager
     * @param TicketLayoutFactory $ticket_layout_factory
     * @param HierarchyGenerator  $hierarchy_generator
     * @param TicketFieldSettings $ticket_field_settings
     */
    public function __construct(
        CustomFieldManager  $field_manager,
        TicketLayoutFactory $ticket_layout_factory,
        HierarchyGenerator  $hierarchy_generator,
        TicketFieldSettings $ticket_field_settings
    ) {
        $this->field_manager         = $field_manager;
        $this->ticket_layout_factory = $ticket_layout_factory;
        $this->hierarchy_generator   = $hierarchy_generator;
        $this->ticket_field_settings = $ticket_field_settings;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof TicketLayout) {
            throw new UnexpectedTypeException($constraint, TicketLayout::class);
        }

        if (!$value) {
            return;
        }
        if (!$value instanceof Ticket) {
            throw new UnexpectedTypeException($value, Ticket::class);
        }

        // get layout fields
        $ticket_layout  = $this->ticket_layout_factory->getLayoutForTicketForm($value->getDepartment() ?: null, true);
        $layout         = $constraint->isAgent() ? $ticket_layout->getAgentLayout() : $ticket_layout->getUserLayout();
        $allowed_fields = [];

        foreach ($layout as $field) {
            /** @var LayoutField $field */
            if ($field->hasCriteria() && !$field->getCriteria()->isTicketMatch($value)) {
                continue;
            }

            $allowed_fields[] = $field;
        }

        // check for layout fields data (required and validation)
        foreach ($allowed_fields as $field) {
            switch ($field->getFieldType()) {
                case FormFields::PRODUCT:
                    $this->validateSetProperty(
                        $value,
                        'product',
                        $this->ticket_field_settings->canProductBeDisplayed(),
                        $this->ticket_field_settings->isProductRequired($constraint->isAgent())
                    );
                    break;
                case FormFields::CATEGORY:
                    $this->validateSetProperty(
                        $value,
                        'category',
                        $this->ticket_field_settings->canCategoryBeDisplayed(),
                        $this->ticket_field_settings->isCategoryRequired($constraint->isAgent())
                    );
                    break;
                case FormFields::PRIORITY:
                    $this->validateSetProperty(
                        $value,
                        'priority',
                        $this->ticket_field_settings->canPriorityBeDisplayed(),
                        $this->ticket_field_settings->isPriorityRequired($constraint->isAgent())
                    );
                    break;
                case FormFields::WORKFLOW:
                    $this->validateSetProperty(
                        $value,
                        'workflow',
                        $this->ticket_field_settings->canWorkflowBeDisplayed(),
                        $this->ticket_field_settings->isWorkflowRequired($constraint->isAgent())
                    );
                    break;
                case FormFields::TICKET_FIELD:
                    $def = $this->field_manager->getCustomTicketFieldById($field->getFieldId());
                    if ($def) {
                        $this->validateCustomField($constraint, $value, $def);
                    }

                    break;
                case FormFields::USER_FIELD:
                    $def = $this->field_manager->getCustomPersonFieldById($field->getFieldId());
                    if ($def) {
                        $this->validateCustomField($constraint, $value, $def);
                    }

                    break;
                case FormFields::ORG_FIELD:
                    $def = $this->field_manager->getCustomOrganizationFieldById($field->getFieldId());
                    if ($def) {
                        $this->validateCustomField($constraint, $value, $def);
                    }

                    break;
            }
        }
    }

    /**
     * @param Ticket $ticket
     * @param string $property
     * @param bool   $can_set
     * @param bool   $required
     */
    private function validateSetProperty(Ticket $ticket, $property, $can_set, $required)
    {
        $value   = PropertyAccess::createPropertyAccessor()->getValue($ticket, $property);
        $context = $this->getContext();

        if ($can_set && $required) {
            $validator = $context->getValidator()->inContext($context);
            $validator->atPath($property)->validate($value, [
                new Assert\NotBlank(),
            ]);
        }
    }

    /**
     * @param TicketLayout      $constraint
     * @param Ticket            $ticket
     * @param CustomDefAbstract $custom_def
     */
    private function validateCustomField(TicketLayout $constraint, Ticket $ticket, CustomDefAbstract $custom_def)
    {
        if (!$custom_def->isEnabled()) {
            // field is disabled
            return;
        }
        if ($custom_def->getOption('agent_validation_resolve') && !$ticket->isResolved()) {
            // no validation, its only on resolve
            return;
        }

        $property_path = $this->getCustomDefPath($custom_def);
        $custom_data   = $this->getCustomDefData($ticket, $custom_def);

        $constraint = new AppAssert\CustomField\CustomData([
            'custom_def' => $custom_def,
            'context'    => $constraint->context,
        ]);

        // Store constraints to prevent duplicates of spl_object_hash()
        $this->constraints[] = $constraint;

        $context   = $this->getContext();
        $validator = $context->getValidator()->inContext($context);
        $validator
            ->atPath($property_path)
            ->validate($custom_data, [$constraint])
        ;
    }

    /**
     * @param CustomDefAbstract $custom_def
     *
     * @return string
     */
    private function getCustomDefPath(CustomDefAbstract $custom_def)
    {
        if ($custom_def instanceof CustomDefTicket) {
            return 'custom_data';
        } elseif ($custom_def instanceof CustomDefPerson) {
            return 'person.custom_data';
        } elseif ($custom_def instanceof CustomDefOrganization) {
            return'organization.custom_data';
        }

        return '';
    }

    /**
     * @param Ticket            $ticket
     * @param CustomDefAbstract $custom_def
     *
     * @return ArrayCollection
     */
    private function getCustomDefData(Ticket $ticket, CustomDefAbstract $custom_def)
    {
        if ($custom_def instanceof CustomDefTicket) {
            return $ticket->getCustomData();
        } elseif ($custom_def instanceof CustomDefPerson && $ticket->getPerson()) {
            return $ticket->getPerson()->getCustomData();
        } elseif ($custom_def instanceof CustomDefOrganization && $ticket->getOrganization()) {
            return $ticket->getOrganization()->getCustomData();
        }

        return new ArrayCollection();
    }

    /**
     * @return \Symfony\Component\Validator\Context\ExecutionContext
     */
    private function getContext()
    {
        return $this->context;
    }
}
