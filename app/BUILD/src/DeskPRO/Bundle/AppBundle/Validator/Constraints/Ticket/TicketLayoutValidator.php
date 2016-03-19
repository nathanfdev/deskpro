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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Ticket;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyGenerator;
use DeskPRO\Bundle\AppBundle\Ticket\TicketFieldSettings;
use DeskPRO\Bundle\AppBundle\Ticket\TicketLayoutFactory;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class TicketLayoutValidator.
 */
class TicketLayoutValidator extends ConstraintValidator
{
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
     * Constructor.
     *
     * @param TicketLayoutFactory $ticket_layout_factory
     * @param HierarchyGenerator  $hierarchy_generator
     * @param TicketFieldSettings $ticket_field_settings
     */
    public function __construct(
        TicketLayoutFactory $ticket_layout_factory,
        HierarchyGenerator  $hierarchy_generator,
        TicketFieldSettings $ticket_field_settings
    ) {
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

        $ticket_layout = $this->ticket_layout_factory->getLayoutForTicketForm($value->getDepartment() ?: null);
        $layout        = $constraint->isAgent() ? $ticket_layout->getAgentLayout() : $ticket_layout->getUserLayout();

        /** @var LayoutField $layout_field */
        foreach ($layout as $layout_field) {
            if ($layout_field->hasCriteria() && !$layout_field->getCriteria()->isTicketMatch($value)) {
                continue;
            }

            switch ($layout_field->getFieldType()) {
                case FormFields::PRODUCT:
                    $this->validateSetProperty(
                        $value,
                        'product',
                        $this->ticket_field_settings->canProductBeDisplayed(),
                        $this->ticket_field_settings->isProductRequired()
                    );
                    break;
                case FormFields::CATEGORY:
                    $this->validateSetProperty(
                        $value,
                        'category',
                        $this->ticket_field_settings->canCategoryBeDisplayed(),
                        $this->ticket_field_settings->isCategoryRequired()
                    );
                    break;
                case FormFields::PRIORITY:
                    $this->validateSetProperty(
                        $value,
                        'priority',
                        $this->ticket_field_settings->canPriorityBeDisplayed(),
                        $this->ticket_field_settings->isPriorityRequired()
                    );
                    break;
                case FormFields::WORKFLOW:
                    $this->validateSetProperty(
                        $value,
                        'workflow',
                        $this->ticket_field_settings->canWorkflowBeDisplayed(),
                        $this->ticket_field_settings->isWorkflowRequired()
                    );
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
        $value = PropertyAccess::createPropertyAccessor()->getValue($ticket, $property);

        if ($can_set && $required && !$value) {
            $this->addError('Select a '.$property, $property);
        }
    }

    /**
     * @return \Symfony\Component\Validator\Context\ExecutionContext
     */
    private function getContext()
    {
        return $this->context;
    }

    /**
     * @param string $error
     * @param string $path
     */
    private function addError($error, $path)
    {
        $this
            ->getContext()
            ->buildViolation($error)
            ->atPath($path)
            ->addViolation()
        ;
    }
}
