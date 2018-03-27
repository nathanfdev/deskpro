<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Triggers;

use Application\DeskPRO\Criteria\CriteriaTermInterface;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\Triggers\Terms\TriggerTermComposite;
use Application\DeskPRO\Tickets\Triggers\Terms\TriggerTermInterface;
use DpSys\LowError\SystemErrorHandler;
use JMS\Serializer\Annotation as JMS;
use Orb\Types\JsonObjectSerializable;

/**
 * This is a wrapper around a TriggerTermComposite that is able to serialize.
 * Used as the serialized object in TicketTrigger records.
 *
 * While any `TriggerTermInterface` can be used with he trigger system, we can only actually
 * *save* the term to the db if it also implements the standard CriteriaTermInterface which defines
 * a standard interface for getting a term name and options (so we can recreate a term object again).
 *
 * @JMS\ExclusionPolicy("all")
 */
class TriggerTerms implements \Serializable, TriggerTermInterface, JsonObjectSerializable, \Countable
{
    /**
     * @var TriggerTermComposite
     */
    private $criteria;

    /**
     * @var TermFactory
     */
    private $term_factory;

    public function __construct()
    {
        $this->criteria = new TriggerTermComposite();
        $this->criteria->setOperator(TriggerTermComposite::OP_OR);
        $this->term_factory = new TermFactory();
    }

    /**
     * @param TriggerTermInterface $term
     *
     * @throws \InvalidArgumentException
     */
    public function addTerm(TriggerTermInterface $term)
    {
        if (!($term instanceof CriteriaTermInterface) && !($term instanceof TriggerTermComposite)) {
            $class_name = get_class($term);
            throw new \InvalidArgumentException("TriggerCriteria can only manage terms terms that implement CriteriaTermInterface. Invalid class: $class_name");
        }
        $this->criteria->add($term);
    }

    /**
     * @param array $term_info
     *
     * @throws \InvalidArgumentException
     */
    public function addTermFromArray(array $term_info)
    {
        if (isset($term_info['set_terms'])) {
            $composite = new TriggerTermComposite([], TriggerTermComposite::OP_AND);
            foreach ($term_info['set_terms'] as $ti) {
                $t = $this->getTermFromArray($ti);
                $composite->add($t);
            }

            $this->addTerm($composite);
        } else {
            $term = $this->getTermFromArray($term_info);
            $this->addTerm($term);
        }
    }

    /**
     * @param array $term_info
     *
     * @throws \InvalidArgumentException
     */
    public function getTermFromArray(array $term_info)
    {
        return $this->term_factory->createFromArray($term_info);
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     *
     * @return bool
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
    {
        return $this->criteria->isTriggerMatch($ticket, $context);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->criteria);
    }

    /**
     * @return array
     */
    public function exportToArray()
    {
        $data = [];

        $data['version'] = $this->getVersion();
        $data['terms']   = $this->getTerms();

        return $data;
    }

    /**
     * Terms version.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("integer")
     * @JMS\SerializedName("version")
     *
     * @return int
     */
    public function getVersion()
    {
        return 1;
    }

    /**
     * Terms themselves.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("array")
     * @JMS\SerializedName("terms")
     *
     * @return array
     */
    public function getTerms()
    {
        $terms = [];
        foreach ($this->criteria->getAll() as $criteria) {
            if ($criteria instanceof TriggerTermComposite) {
                $set_terms = [];
                foreach ($criteria->getAll() as $set_criteria) {
                    if (!($set_criteria instanceof CriteriaTermInterface)) {
                        continue;
                    }

                    $set_terms[] = [
                        'type'    => $set_criteria->getTermType(),
                        'op'      => $set_criteria->getTermOperator(),
                        'options' => $set_criteria->getTermOptions()->all(),
                    ];
                }

                if ($set_terms) {
                    $terms[] = [
                        'set_terms' => $set_terms,
                    ];
                }
            } else {
                if (!($criteria instanceof CriteriaTermInterface)) {
                    continue;
                }

                $terms[] = [
                    'type'    => $criteria->getTermType(),
                    'op'      => $criteria->getTermOperator(),
                    'options' => $criteria->getTermOptions()->all(),
                ];
            }
        }

        return $terms;
    }

    /**
     * @param array $data
     */
    public function importFromArray(array $data)
    {
        foreach ($data['terms'] as $term_info) {
            $this->addTermFromArray($term_info);
        }
    }

    /**
     * @return string
     */
    public function exportToJson()
    {
        return json_encode($this->exportToArray());
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return $this->exportToJson();
    }

    /**
     * @return array
     */
    public function serializeJsonArray()
    {
        return $this->exportToArray();
    }

    /**
     * @param array $data
     *
     * @return TriggerTerms
     */
    public static function unserializeJsonArray(array $data)
    {
        $obj = new self();
        foreach ($data['terms'] as $term_info) {
            try {
                $obj->addTermFromArray($term_info);
            } catch (\Exception $e) {
                if (!empty($term_info['type'])) {
                    SystemErrorHandler::logException($e, false, md5('triggerterm_'.$term_info['type']));
                }
            }
        }

        return $obj;
    }

    /**
     * @param string $data
     */
    public function unserialize($data)
    {
        $data = json_decode($data, true);
        $this->__construct();

        foreach ($data['terms'] as $term_info) {
            try {
                $this->addTermFromArray($term_info);
            } catch (\Exception $e) {
                if (!empty($term_info['type'])) {
                    SystemErrorHandler::logException($e, false, md5('triggerterm_'.$term_info['type']));
                }
            }
        }
    }
}
