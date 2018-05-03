<?php

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

    const FORM_TYPE_WIDGET = 'widget';

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
    private $previousLayout;

    /**
     * @var \Symfony\Component\Form\FormInterface
     */
    private $form;

    /**
     * @var mixed|null
     */
    private $submittedData;

    /**
     * @var string|null
     */
    private $formType;

    /**
     * @var bool
     */
    private $fullLayout = false;

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
        if (isset($options['form_type'])) {
            $context->formType = $options['form_type'];
        }

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

        $context = new self($form, $data, $options['layout_factory']($data->getDepartment()), $event->getData());

        if ($form->has(FormFields::DEPARTMENT) && isset($submitted[FormFields::DEPARTMENT])) {
            $context->setNewLayout($options['layout_factory']($submitted[FormFields::DEPARTMENT]));
        }
        if (isset($options['form_type'])) {
            $context->formType = $options['form_type'];
        }

        return $context;
    }

    /**
     * Constructor.
     *
     * @param FormInterface $form
     * @param Ticket        $ticket
     * @param TicketLayout  $layout
     * @param mixed         $submittedData
     */
    public function __construct(FormInterface $form, Ticket $ticket, TicketLayout $layout, $submittedData = null)
    {
        $this->form           = $form;
        $this->ticket         = $ticket;
        $this->layout         = $layout;
        $this->previousLayout = $layout;
        $this->submittedData  = $submittedData;
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
        return $this->isAgentView() ? $this->layout->getAgentLayout() : $this->layout->getUserLayout();
    }

    /**
     * @return Layout
     */
    public function getPreviouslyActiveLayout()
    {
        return $this->isAgentView() ? $this->previousLayout->getAgentLayout() : $this->previousLayout->getUserLayout();
    }

    /**
     * @return bool
     */
    public function hadLayout()
    {
        return count($this->getPreviouslyActiveLayout()->all()) > 0;
    }

    /**
     * @return bool
     */
    public function isAgentView()
    {
        return $this->getViewContext() === self::VIEW_AGENT;
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
        switch ($this->getVisibility()) {
            case self::VISIBILITY_NEW:
                return $field->isVisibleOnNew();
            case self::VISIBILITY_EDIT:
                return $field->isVisibleOnEdit();
            case self::VISIBILITY_VIEW:
                return $field->isVisibleOnView();
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
        $this->previousLayout = $this->layout;
        $this->layout         = $destination_layout;
    }

    /**
     * @return TicketLayout
     */
    public function getPreviousLayout()
    {
        return $this->previousLayout;
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
     * @return Person
     */
    public function getPerson()
    {
        return $this->getOption('person');
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
     * @return mixed|null
     */
    public function getSubmittedData()
    {
        return $this->submittedData;
    }

    /**
     * @return null|string
     */
    public function isWidgetType()
    {
        return $this->formType === self::FORM_TYPE_WIDGET;
    }

    /**
     * @return bool
     */
    public function isFullLayout()
    {
        return $this->fullLayout;
    }

    /**
     * @param bool $fullLayout
     */
    public function setFullLayout($fullLayout)
    {
        $this->fullLayout = $fullLayout;
    }
}
