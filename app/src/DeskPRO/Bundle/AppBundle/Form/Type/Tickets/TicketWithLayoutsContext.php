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
namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketLayout;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\NewSettings\SettingsBag;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutField;
use Symfony\Component\Form\FormInterface;

/**
 * Often times there is a significant amount of data surrounding a form that needs to be kept in context.
 */
class TicketWithLayoutsContext
{
    /**
     * visibilities.
     */
    const VISIBILITY_NEW  = 'new';
    const VISIBILITY_EDIT = 'edit';
    const VISIBILITY_VIEW = 'view';

    /**
     * view contexts.
     */
    const VIEW_USER  = 'user';
    const VIEW_AGENT = 'agent';

    /**
     * @var \Application\DeskPRO\Entity\Ticket
     */
    private $ticket;

    /**
     * @var TicketLayout
     */
    private $layout;

    /**
     * @var TicketLayout
     */
    private $previous_layout;

    /**
     * @var string
     */
    private $visibility;

    /**
     * @var \Symfony\Component\Form\FormInterface
     */
    private $form;

    /**
     * @var bool keeps track of if we have already added a captcha to the form or not
     */
    private $captcha_exists_on_form;

    /**
     * @var array passed as a hidden form field, it tells us what fields were visible to a user on submit
     *            this helps us decide if we need to re-render or not
     */
    private $previously_displayed_fields;

    /**
     * Constructor.
     *
     * @param FormInterface $form
     * @param Ticket        $ticket
     * @param TicketLayout  $layout
     * @param array         $previously_displayed_fields
     */
    public function __construct(FormInterface $form, Ticket $ticket, TicketLayout $layout, array $previously_displayed_fields)
    {
        $this->form                        = $form;
        $this->ticket                      = $ticket;
        $this->layout                      = $layout;
        $this->previous_layout             = $layout;
        $this->captcha_exists_on_form      = false;
        $this->previously_displayed_fields = $previously_displayed_fields;
    }

    /**
     * The ticket layout we are using for this form.
     *
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
        return self::VIEW_AGENT === $this->getViewContext() ? $this->layout->agent_layout : $this->layout->user_layout;
    }

    /**
     * @return Layout
     */
    public function getPreviouslyActiveLayout()
    {
        return self::VIEW_AGENT === $this->getViewContext() ? $this->layout->agent_layout : $this->layout->user_layout;
    }

    /**
     * Returns "user" or "agent".
     *
     * @return string
     */
    public function getViewContext()
    {
        return $this->getOption('ticket_view_context', self::VIEW_USER);
    }

    /**
     * The view, such as "new", "edit", "view" (constants of this class).
     *
     * @return string
     */
    public function getVisibility()
    {
        return $this->getOption('ticket_visibility');
    }

    /**
     * Does this field have the right visibility, given our context?
     *
     * @param LayoutField $field
     *
     * @return bool
     */
    public function hasValidVisibility(LayoutField $field)
    {
        if (self::VISIBILITY_NEW === $this->visibility && !$field->isVisibleOnNew()) {
            return false;
        }
        if (self::VISIBILITY_EDIT === $this->visibility && !$field->isVisibleOnEdit()) {
            return false;
        }
        if (self::VISIBILITY_VIEW === $this->visibility && !$field->isVisibleOnView()) {
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
        return $this->getOption('person');
    }

    /**
     * @return bool
     */
    public function forApi()
    {
        return $this->getOption('for_api', false);
    }

    /**
     * @return Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @param TicketLayout $destination_layout
     */
    public function setNewLayout(TicketLayout $destination_layout)
    {
        $this->previous_layout = $this->layout;
        $this->layout          = $destination_layout;
    }

    /**
     * @return TicketLayout
     */
    public function getPreviousLayout()
    {
        return $this->previous_layout;
    }

    /**
     * @return TicketMessage
     */
    public function getMessage()
    {
        $ticket = $this->getTicket();
        if (!$ticket) {
            throw new \RuntimeException('Ticket is not defined');
        }

        if ($ticket->messages->count()) {
            return $ticket->messages->first();
        }

        $ticket_message = new TicketMessage();
        $ticket_message
            ->setPerson($this->getPerson())
            ->setMessage('')
        ;

        $ticket->addMessage($ticket_message);

        return $ticket_message;
    }

    /**
     * @param TicketLayout $layout
     */
    public function setLayout(TicketLayout $layout)
    {
        $this->layout = $layout;
    }

    /**
     * @param TicketLayout $previous_layout
     */
    public function setPreviousLayout(TicketLayout $previous_layout)
    {
        $this->previous_layout = $previous_layout;
    }

    /**
     * @return bool
     */
    public function doesCaptchaExistOnForm()
    {
        return $this->captcha_exists_on_form;
    }

    /**
     * @param bool $bool
     */
    public function setCaptchaExistsOnForm($bool)
    {
        $this->captcha_exists_on_form = (bool) $bool;
    }

    /**
     * @return array
     */
    public function getPreviouslyDisplayedFields()
    {
        return $this->previously_displayed_fields;
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getSetting($name, $default = null)
    {
        /** @var SettingsBag $settings_bag */
        $settings_bag = $this->form->getConfig()->getOption('settings');

        return $settings_bag->get($name, $default);
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getOption($name, $default = null)
    {
        return $this->form->getConfig()->getOption($name, $default);
    }
}
