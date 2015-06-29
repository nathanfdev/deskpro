<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Ticket;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\TicketLayout;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\PortalBundle\Form\FormFields;
use Doctrine\ORM\EntityManager;

class TicketLayoutFactory
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entity_manager;

    public function __construct(EntityManager $entity_manager)
    {
        $this->entity_manager = $entity_manager;
    }

    /**
     * Used in the TicketType form type to detect the layout it should use, given the selected dept (or null for initial layout).
     *
     * @param Department|int $department the department entity or its ID
     *
     * @return TicketLayout
     */
    public function getLayoutForTicketForm($department = null)
    {
        $layout = null;
        if ($department) {
            $layout = $this->entity_manager->createQuery('SELECT l FROM DeskPRO:TicketLayout l WHERE l.department = :department')
                ->setParameter('department', $department)
                ->getOneOrNullResult();
        }

        if (!$layout) {
            $layout = $this->getInitialLayout();
        }

        // verify that the user layout has a subject, message, and user email
        $this->verifyRequiredFields($layout->user_layout);
        $this->verifyRequiredFields($layout->agent_layout);

        return $layout;
    }

    protected function verifyRequiredFields(Layout $layout)
    {
        $required_fields = array(
            FormFields::SUBJECT => 0,
            FormFields::MESSAGE => 0,
            FormFields::USER_EMAIL => 0,
        );

        /** @var \Application\DeskPRO\TicketLayout\LayoutField $layout_field */
        foreach ($layout as $layout_field) {
            if (array_key_exists($layout_field->getFieldType(), $required_fields)) {
                $required_fields[$layout_field->getFieldType()]++;
            }
        }

        // if any are still 0, add them to the layout
        foreach($required_fields as $field_type => $count) {
            if (0 === $count) {
                $new = new LayoutField($field_type);
                $new->enableOnNew();
                $new->enableOnEdit();
                $new->enableOnView();
                $layout->add($new);
            }
        }
    }

    /**
     * Gets a combination of all ticket layouts. This is used to output a 'full' form with every field,
     * which is used by JS to dynamically update the UI as a user changes options.
     *
     * @return TicketLayout
     */
    public function getFullLayoutForTicketForm()
    {
        $layout = new TicketLayout();

        /** @var TicketLayout[] $all_layouts */
        $all_layouts = $this->entity_manager->createQuery("SELECT l FROM DeskPRO:TicketLayout l")->execute();

        foreach ($all_layouts as $l) {
            foreach ($l->user_layout->all() as $f) {
                $layout->user_layout->add($f);
            }
            foreach ($l->agent_layout->all() as $f) {
                $layout->agent_layout->add($f);
            }
        }

        return $layout;
    }

    /**
     * @return TicketLayout
     */
    public function getInitialLayout()
    {
        return $this->entity_manager->createQuery('SELECT l FROM DeskPRO:TicketLayout l WHERE l.department IS NULL')
            ->getOneOrNullResult();
    }
}
