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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketLayout;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldRenderer\FieldRendererInterface;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldResolver\AbstractFieldResolver;
use Symfony\Component\Form\FormEvent;
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
     * @var \Symfony\Component\Form\FormInterface
     */
    private $form;

    /**
     * @param FormEvent $event
     *
     * @return $this
     */
    public static function createOnPreSetData(FormEvent $event)
    {
        $form    = $event->getForm();
        $data    = $event->getData();
        $options = $form->getConfig()->getOptions();

        $context = new self($form, $data, new TicketLayout());
        $context->setNewLayout($options['layout_factory']($data->getDepartment()));

        return $context;
    }

    /**
     * @param FormEvent $event
     *
     * @return TicketWithLayoutsContext
     */
    public static function createOnPreSubmit(FormEvent $event)
    {
        $form      = $event->getForm();
        $data      = $form->getData();
        $options   = $form->getConfig()->getOptions();
        $submitted = $event->getData();

        $context = new self($form, $data, $options['layout_factory']($data->getDepartment()));

        if ($form->has(FormFields::DEPARTMENT) && isset($submitted[FormFields::DEPARTMENT])) {
            $context->setNewLayout($options['layout_factory']($submitted[FormFields::DEPARTMENT]));
        }

        return $context;
    }

    /**
     * Constructor.
     *
     * @param FormInterface $form
     * @param Ticket        $ticket
     * @param TicketLayout  $layout
     */
    public function __construct(FormInterface $form, Ticket $ticket, TicketLayout $layout)
    {
        $this->form            = $form;
        $this->ticket          = $ticket;
        $this->layout          = $layout;
        $this->previous_layout = $layout;
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
        return self::VIEW_AGENT === $this->getViewContext() ? $this->previous_layout->agent_layout : $this->previous_layout->user_layout;
    }

    /**
     * @return bool
     */
    public function hadLayout()
    {
        return count($this->getPreviouslyActiveLayout()->all()) > 0;
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
     * @return bool
     */
    public function isAgentView()
    {
        return $this->getViewContext() === self::VIEW_AGENT;
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
        if (self::VISIBILITY_NEW === $this->getVisibility() && !$field->isVisibleOnNew()) {
            return false;
        }
        if (self::VISIBILITY_EDIT === $this->getVisibility() && !$field->isVisibleOnEdit()) {
            return false;
        }
        if (self::VISIBILITY_VIEW === $this->getVisibility() && !$field->isVisibleOnView()) {
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
     * @param $field
     *
     * @return bool
     */
    public function fieldWasDisplayedBefore(LayoutField $field)
    {
        return $this->getPreviouslyActiveLayout()->has($field->getId());
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

    /**
     * @return AbstractFieldResolver
     */
    public function getFieldResolver()
    {
        return $this->getOption('field_resolver');
    }

    /**
     * @return FieldRendererInterface
     */
    public function getFieldRenderer()
    {
        return $this->getOption('field_renderer');
    }
}
