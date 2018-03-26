<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\CustomFields\FieldManager;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\Ticket;

class PeopleFieldAction extends AbstractAction
{
    /**
     * @var \Application\DeskPRO\CustomFields\FieldManager
     */
    protected $field_manager;

    /**
     * @var \Application\DeskPRO\Entity\CustomDefPerson
     */
    protected $field_def;

    /**
     * @var mixed
     */
    protected $set_value;

    public function __construct(FieldManager $field_manager, CustomDefPerson $field_def, $set_value)
    {
        $this->field_manager = $field_manager;
        $this->field_def     = $field_def;
        $this->set_value     = $set_value;

        // Backwards compat:
        // Old versions set array directly while newer versions have standard
        // custom_fields array holder
        if (!isset($this->set_value['custom_fields'])) {
            $this->set_value = ['custom_fields' => $this->set_value];
        }
    }

    /**
     * @return \Application\DeskPRO\Entity\CustomDefTicket
     */
    public function getFieldDef()
    {
        return $this->field_def;
    }

    /**
     * @return mixed
     */
    public function getFieldValue()
    {
        return $this->set_value;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        $person = $ticket->getPerson();
        $this->field_manager->saveFormToObject($this->set_value['custom_fields'], $person);
    }

    /**
     * {@inheritdoc}
     */
    public function getApplyActions(Ticket $ticket)
    {
        return [
            [
                'action'          => 'person_field',
                'person_field_id' => $this->field_def->id,
                'value'           => $this->set_value,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function merge(ActionInterface $otherAction)
    {
        return $otherAction;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($as_html = true)
    {
        $tr    = App::getTranslator();
        $title = $this->field_def->title;
        $value = $this->set_value;

        $value = isset($value['custom_fields']['field_'.$this->field_def->getId()]) ? $value['custom_fields']['field_'.$this->field_def->getId()] : '';
        if ($this->field_def->getTypeName() == 'choice') {
            $value_ids = (array) $value;
            $value     = [];
            $titles    = $this->field_def->getAllChildTitles();
            foreach ($value_ids as $id) {
                if (isset($titles[$id])) {
                    $value[] = $titles[$id];
                }
            }
            $value = implode(', ', $value);
        }

        return $tr->phrase('agent.tickets.set_x_to_y_action', ['title' => $title, 'value' => $value]);
    }

    /**
     * {@inheritdoc}
     */
    public function getActionName()
    {
        return get_class($this).'['.$this->field_def->getId().']';
    }
}
