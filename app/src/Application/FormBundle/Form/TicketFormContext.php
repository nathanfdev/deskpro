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

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TicketLayout;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutField;
use Symfony\Component\Form\FormInterface;

/**
 * Often times there is a sifnigifant amount of data surrounding a form that needs to be kept in context.
 */
class TicketFormContext
{
    /**
     * visibilities
     */
    const VISIBILITY_NEW = 'new';
    const VISIBILITY_EDIT = 'edit';
    const VISIBILITY_VIEW = 'view';

    /**
     * view contexts
     */
    const VIEW_USER = 'user';
    const VIEW_AGENT = 'agent';

    /**
     * @var TicketLayout
     */
    private $layout;

    /**
     * @var Layout
     */
    private $active_layout;

    /**
     * @var string
     */
    private $view_context;

    /**
     * @var string
     */
    private $visibility;

    /**
     * @var \Symfony\Component\Form\FormInterface
     */
    private $form;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    private $person;

    /**
     * @param FormInterface $form
     * @param Person        $person
     * @param TicketLayout  $layout       the ticket layout we are using for this form
     * @param string        $view_context - "user" or "agent"?
     * @param string        $visibility   the view, such as "new", "edit", "view" (contants of this class)
     */
    public function __construct(FormInterface $form, Person $person, TicketLayout $layout, $view_context, $visibility)
    {
        $this->form = $form;
        $this->person = $person;
        $this->layout = $layout;
        $this->view_context = $view_context;
        $this->visibility = $visibility;
        $this->active_layout = ('agent' === $view_context) ? $layout->agent_layout : $layout->user_layout;
    }

    /**
     * @return TicketLayout
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @return Layout
     */
    public function getActiveLayout()
    {
        return $this->active_layout;
    }

    /**
     * @return string "user" or "agent"
     */
    public function getViewContext()
    {
        return $this->view_context;
    }

    /**
     * @return string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * Does this field have the right visibility, given our context?
     *
     * @param LayoutField $field
     * @return bool
     */
    public function hasValidVisibility(LayoutField $field)
    {
        if (TicketFormContext::VISIBILITY_NEW === $this->visibility && !$field->isVisibleOnNew()) {
            return false;
        }
        if (TicketFormContext::VISIBILITY_EDIT === $this->visibility && !$field->isVisibleOnEdit()) {
            return false;
        }
        if (TicketFormContext::VISIBILITY_VIEW === $this->visibility && !$field->isVisibleOnView()) {
            return false;
        }

        return true;
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }
}
