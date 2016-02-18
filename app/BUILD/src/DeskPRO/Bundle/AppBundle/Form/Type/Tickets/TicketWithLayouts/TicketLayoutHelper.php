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
namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts;

use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyNode;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;

/**
 * Class TicketLayoutHelper.
 */
class TicketLayoutHelper extends AbstractType
{
    /**
     * @var EntityManager
     */
    protected $em;

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
     * @param TicketWithLayoutsContext $context
     *
     * @return bool
     */
    public function canProductBeDisplayed(TicketWithLayoutsContext $context)
    {
        if (!$context->getSetting('core.use_product', false)) {
            return false;
        }

        /** @var \Application\DeskPRO\EntityRepository\Product $repository */
        $repository = $this->em->getRepository('DeskPRO:Product');

        return $repository->countAll() > 0;
    }

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return bool
     */
    public function canPriorityBeDisplayed(TicketWithLayoutsContext $context)
    {
        if (!$context->getSetting('core.use_ticket_priority', false)) {
            return false;
        }

        /** @var \Application\DeskPRO\EntityRepository\TicketPriority $repository */
        $repository = $this->em->getRepository('DeskPRO:TicketPriority');

        return $repository->countAll() > 0;
    }

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return bool
     */
    public function canCategoryBeDisplayed(TicketWithLayoutsContext $context)
    {
        if (!$context->getSetting('core.use_ticket_category', false)) {
            return false;
        }

        /** @var \Application\DeskPRO\EntityRepository\TicketCategory $repository */
        $repository = $this->em->getRepository('DeskPRO:TicketCategory');

        return $repository->countAll() > 0;
    }

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return bool
     */
    public function canWorkflowBeDisplayed(TicketWithLayoutsContext $context)
    {
        if (!$context->getSetting('core.use_ticket_workflow', false)) {
            return false;
        }

        /** @var \Application\DeskPRO\EntityRepository\TicketWorkflow $repository */
        $repository = $this->em->getRepository('DeskPRO:TicketWorkflow');

        return $repository->countAll() > 0;
    }

    /**
     * @param LayoutField $field
     * @param array       $extracted_data
     *
     * @return bool
     */
    public function fieldHasCriteriaAndCriteriaDoesNOTMatch(LayoutField $field, $extracted_data)
    {
        return $field->getCriteria() && !$field->getCriteria()->isSubmittedDataMatch($extracted_data);
    }

    /**
     * @param LayoutField $field
     * @param array       $extracted_data
     *
     * @return bool
     */
    public function fieldHasCriteriaAndItDOESMatch(LayoutField $field, $extracted_data)
    {
        return $field->getCriteria() && $field->getCriteria()->isSubmittedDataMatch($extracted_data);
    }

    /**
     * @param LayoutField $field
     * @param array       $extracted_data
     *
     * @return bool
     */
    public function fieldDoesNotHaveCriteriaOrHasCriteriaAndMatches(LayoutField $field, $extracted_data)
    {
        return !$field->getCriteria() || ($field->getCriteria() && $field->getCriteria()->isSubmittedDataMatch($extracted_data));
    }

    /**
     * Submitted choice values are not submitted with the entity Id. Instead we are given the choice list key.
     *
     * This inspects the submitted data on our form and gives us data we're interested in.
     *
     * @param array                    $submitted_data
     * @param TicketWithLayoutsContext $context
     *
     * @return array the form key and its selected entity ID (or null if not submitted)
     */
    public function getTicketDataIds(array $submitted_data, TicketWithLayoutsContext $context)
    {
        $form       = $context->getForm();
        $final_data = [];
        $keys       = [
            FormFields::DEPARTMENT,
            FormFields::PRODUCT,
            FormFields::CATEGORY,
            FormFields::WORKFLOW,
            FormFields::PRIORITY,
        ];

        foreach ($keys as $key) {
            if (array_key_exists($key, $submitted_data) && $form->has($key)) {
                $submitted_value = $submitted_data[$key];
                $choice          = current($form->get($key)->getConfig()->getOption('choice_list')->getChoicesForValues([$submitted_value]));

                if ($choice instanceof HierarchyNode) {
                    $choice = $choice->getData();
                }

                if ($choice) {
                    $final_data[$key] = $choice->getId();
                } else {
                    $final_data[$key] = null;
                }
            } else {
                $final_data[$key] = null;
            }
        }

        return $final_data;
    }

    /**
     * @param Layout                   $new_layout
     * @param TicketWithLayoutsContext $context
     * @param array                    $extracted_data
     * @param array                    $fields_to_remove
     * @param array                    $additional_fields
     *
     * @return array
     */
    public function useLayoutCriteriaToDetermineDynamicLayoutChanges(Layout $new_layout, TicketWithLayoutsContext $context, $extracted_data, $fields_to_remove, $additional_fields)
    {
        // DEPENDENT FIELDS
        // find fields that should be rendered, but weren't before, via criteria with recently submitted data
        $fields_requiring_rerender = [];
        foreach ($new_layout->all() as $field) {
            if ($context->fieldWasDisplayedBefore($field)) {
                // this field was displayed before. should it continue to be displayed?
                if ($this->fieldHasCriteriaAndCriteriaDoesNOTMatch($field, $extracted_data)) {
                    $fields_to_remove[] = $field;
                }
            } else {
                // this field was not displayed before, but should it be added and the form re-rendered?
                if ($this->fieldDoesNotHaveCriteriaOrHasCriteriaAndMatches($field, $extracted_data)) {
                    if (!in_array($field, $additional_fields)) {
                        $additional_fields[] = $field;
                    }
                    $fields_requiring_rerender[] = $field;
                }
            }
        }

        return [$fields_requiring_rerender, $fields_to_remove, $additional_fields];
    }
}
