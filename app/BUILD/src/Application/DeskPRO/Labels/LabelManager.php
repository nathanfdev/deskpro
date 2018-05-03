<?php

/**
 * DeskPRO.
 *
 * @category ORM
 */

namespace Application\DeskPRO\Labels;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\EntityRepository\LabelDef;

class LabelManager
{
    /** @var DomainObject */
    protected $entity;
    /** @var string */
    protected $label_entity_name;
    /** @var string */
    protected $labels_property;
    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    public function __construct($entity, $label_entity_name, $labels_property = 'labels')
    {
        $this->entity                 = $entity;
        $this->label_entity_name      = $label_entity_name;
        $this->label_entity_classname = str_replace('DeskPRO:', 'Application\\DeskPRO\\Entity\\', $this->label_entity_name);
        $this->labels_property        = $labels_property;

        // todo construct in factory method, em injection
        $this->em = App::getOrm();
    }

    public function createLabelEntity()
    {
        $label = new $this->label_entity_classname();

        return $label;
    }

    public function removeLabel($label)
    {
        $label = self::normalizeLabel($label);

        foreach ($this->entity[$this->labels_property] as $k => $labelobj) {
            if ($labelobj['label'] == $label) {
                if ($this->entity instanceof Ticket || $this->entity instanceof Person) {
                    $this->entity->removeLabelByString($label);
                } else {
                    $this->entity[$this->labels_property]->remove($k);
                }

                if ($this->entity instanceof Ticket && $this->entity->getTicketLogger()) {
                    $this->entity->getTicketLogger()->recordMultiPropertyChanged('label_removed', $label, null);
                }

                $type_name = strtolower(\Orb\Util\Util::getBaseClassname($this->entity)).'s';
                if ($type_name == 'chatconversations') {
                    $type_name = 'chat';
                }

                /** @var LabelDef $rep */
                $rep = $this->em->getRepository('DeskPRO:LabelDef');
                if ($definition = $rep->getDefinition($type_name, $label)) {
                    $rep->updateDefinitionUsages($definition);
                }
                // todo should be handled here '$this->entity[$this->labels_property]->remove($k);'
                $this->em->remove($labelobj);

                return $labelobj;
            }
        }

        return;
    }

    public function removeLabels(array $labels)
    {
        foreach ($labels as $label) {
            $this->removeLabel($label);
        }
    }

    public function addLabel($label, $skip_corrections = false)
    {
        $label = self::normalizeLabel($label);

        if (!$skip_corrections) {
            $rep    = $this->em->getRepository('DeskPRO:LabelDef');
            $type   = $rep->getTypeByEntityName($this->label_entity_name);
            $labels = $rep->correctLabels($type, [$label], true);
            $label  = array_pop($labels);
        }

        foreach ($this->entity[$this->labels_property] as $labelobj) {
            if ($labelobj['label'] == $label) {
                return $labelobj;
            }
        }

        $labelobj          = $this->createLabelEntity();
        $labelobj['label'] = $label;
        $this->entity->addLabel($labelobj);

        if ($this->entity instanceof Ticket && $this->entity->getTicketLogger()) {
            $this->entity->getTicketLogger()->recordMultiPropertyChanged('label_added', null, $label);
        }

        $this->em->persist($labelobj);

        return $labelobj;
    }

    public function addLabels(array $labels)
    {
        $rep    = $this->em->getRepository('DeskPRO:LabelDef');
        $type   = $rep->getTypeByEntityName($this->label_entity_name);
        $labels = $rep->correctLabels($type, $labels, true);

        foreach ($labels as $label) {
            $this->addLabel($label, true);
        }
    }

    public function getLabelsArray()
    {
        $labels = [];
        foreach ($this->entity[$this->labels_property] as $label) {
            $labels[] = $label['label'];
        }

        return $labels;
    }

    public function hasLabel($label)
    {
        $label_test = self::normalizeLabel($label);

        foreach ($this->entity[$this->labels_property] as $label) {
            if ($label['label'] == $label_test) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param null $labels
     */
    public function setLabelsArray($labels = null)
    {
        // back compatibility
        if (!$labels) {
            $labels = [];
        } elseif (!is_array($labels)) {
            $labels = explode(',', $labels);
        }

        $labels_raw = $labels;
        $labels     = [];

        foreach ($labels_raw as $label) {
            $label = self::normalizeLabel($label);
            if ($label) {
                $labels[] = $label;
            }
        }

        $existing_labels = $this->getLabelsArray();
        $added           = array_diff($labels, $existing_labels);
        $removed         = array_diff($existing_labels, $labels);

        /** @var LabelDef $rep */
        $rep  = $this->em->getRepository('DeskPRO:LabelDef');
        $type = $rep->getTypeByEntityName($this->label_entity_name);

        if (App::getCurrentPerson() && !App::getCurrentPerson()->isGuest() && App::getCurrentPerson()->is_agent) {
            $person = App::getCurrentPerson();
            switch ($this->label_entity_name) {
                case 'DeskPRO:LabelTicket':           $perm = $person->hasPerm('agent_tickets.create_labels'); break;
                case 'DeskPRO:LabelPerson':           $perm = $person->hasPerm('agent_people.create_labels'); break;
                case 'DeskPRO:LabelOrganization':     $perm = $person->hasPerm('agent_org.create_labels'); break;
                case 'DeskPRO:LabelChatConversation': $perm = $person->hasPerm('agent_chat.create_labels'); break;
                case 'DeskPRO:LabelArticle':          $perm = $person->hasPerm('agent_publish.articles_create_labels'); break;
                case 'DeskPRO:LabelNews':             $perm = $person->hasPerm('agent_publish.news_create_labels'); break;
                case 'DeskPRO:LabelDownload':         $perm = $person->hasPerm('agent_publish.downloads_create_labels'); break;
                case 'DeskPRO:LabelFeedback':         $perm = $person->hasPerm('agent_publish.feedback_create_labels'); break;
                default: $perm                              = false;
            }
        } else {
            // no user at the moment which means
            // probably cron/trigger
            $perm = true;
        }

        if ($added && !$perm) {
            $added = $rep->correctLabels($type, $added, false);
        } else {
            $added = $rep->correctLabels($type, $added, true);
        }

        foreach ($added as $added_label) {
            $this->addLabel($added_label, true);
        }
        foreach ($removed as $removed_label) {
            $this->removeLabel($removed_label);
        }
    }

    public static function normalizeLabel($label)
    {
        $label = trim($label);
        $label = str_replace(',', '', $label);

        return $label;
    }
}
