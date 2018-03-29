<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Filters;

use Application\DeskPRO\Criteria\CriteriaTermInterface;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\Filters\Terms\FilterTermComposite;
use Application\DeskPRO\Tickets\Filters\Terms\FilterTermInterface;
use DpSys\LowError\SystemErrorHandler;

/**
 * This is a wrapper around a FilterTermComposite that is able to serialize.
 * Used as the serialized object in TicketFilter records.
 *
 * While any `FilterTermInterface` can be used with he trigger system, we can only actually
 * *save* the term to the db if it also implements the standard CriteriaTermInterface which defines
 * a standard interface for getting a term name and options (so we can recreate a term object again).
 */
class FilterTerms implements \Serializable, FilterTermInterface
{
    /**
     * @var FilterTermComposite
     */
    private $criteria;

    /**
     * @var FilterTermFactory
     */
    private $term_factory;

    public function __construct()
    {
        $this->criteria = new FilterTermComposite();
        $this->criteria->setOperator(FilterTermComposite::OP_AND);
        $this->term_factory = new FilterTermFactory();
    }

    /**
     * @return FilterTermInterface[]
     */
    public function getTerms()
    {
        return $this->criteria->getAll();
    }

    /**
     * @param FilterTermInterface $term
     *
     * @throws \InvalidArgumentException
     */
    public function addTerm(FilterTermInterface $term)
    {
        if (!($term instanceof CriteriaTermInterface) && !($term instanceof FilterTermComposite)) {
            $class_name = get_class($term);
            throw new \InvalidArgumentException("FilterTerms can only manage terms terms that implement CriteriaTermInterface. Invalid class: $class_name");
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
            $composite = new FilterTermComposite([], FilterTermComposite::OP_AND);
            foreach ($term_info['set_terms'] as $ti) {
                $t = $this->getTermFromArray($ti);
                $composite->add($t);
            }

            $this->addTerm($composite);
        } else {
            $t = $this->getTermFromArray($term_info);
            $this->addTerm($t);
        }
    }

    /**
     * @param array $term_info
     *
     * @throws \InvalidArgumentException
     */
    private function getTermFromArray(array $term_info)
    {
        return $this->term_factory->createFromArray($term_info);
    }

    /**
     * @param ExecutorContextInterface $context
     *
     * @return \Application\DeskPRO\Tickets\Filters\Terms\FilterQuery|null
     */
    public function getFilterQuery(ExecutorContextInterface $context = null)
    {
        return $this->criteria->getFilterQuery($context);
    }

    /**
     * @return array
     */
    public function exportToArray()
    {
        $data = [];

        $data['version'] = 1;
        $data['terms']   = [];
        foreach ($this->criteria->getAll() as $criteria) {
            if ($criteria instanceof FilterTermComposite) {
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
                    $data['terms'][] = [
                        'set_terms' => $set_terms,
                    ];
                }
            } else {
                if (!($criteria instanceof CriteriaTermInterface)) {
                    continue;
                }

                $data['terms'][] = [
                    'type'    => $criteria->getTermType(),
                    'op'      => $criteria->getTermOperator(),
                    'options' => $criteria->getTermOptions()->all(),
                ];
            }
        }

        return $data;
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
                    SystemErrorHandler::logException($e, false, md5('filter_'.$term_info['type']));
                }
            }
        }
    }
}
