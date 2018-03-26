<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Escalations;

use Application\DeskPRO\Criteria\CriteriaTermInterface;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\Filters\Terms\FilterTermComposite;
use Application\DeskPRO\Tickets\Filters\Terms\FilterTermInterface;

class EscalationTerms implements \Serializable, FilterTermInterface
{
    /**
     * @var FilterTermComposite
     */
    private $criteria;

    public function __construct()
    {
        $this->criteria = new FilterTermComposite();
        $this->criteria->setOperator(FilterTermComposite::OP_OR);
    }

    /**
     * @param FilterTermInterface $term
     *
     * @throws \InvalidArgumentException
     */
    public function addTerm(FilterTermInterface $term)
    {
        if (!($term instanceof CriteriaTermInterface)) {
            $class_name = get_class($term);
            throw new \InvalidArgumentException("EscalationTerms can only manage terms terms that implement CriteriaTermInterface. Invalid class: $class_name");
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
        $type       = $term_info['type'];
        $class_name = "Application\\DeskPRO\\Tickets\\Filters\\Terms\\$type";
        if (!class_exists($class_name)) {
            throw new \InvalidArgumentException("Unknown term $type (could not locate class: $class_name)");
        }

        $term = new $class_name($term_info['op'], $term_info['options']);
        $this->addTerm($term);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterQuery(ExecutorContextInterface $context = null)
    {
        return $this->criteria->getFilterQuery();
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
            if (!($criteria instanceof CriteriaTermInterface)) {
                continue;
            }

            $data['terms'][] = [
                'type'    => $criteria->getTermType(),
                'op'      => $criteria->getTermOperator(),
                'options' => $criteria->getTermOptions(),
            ];
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
    public function serialize()
    {
        return json_encode($this->exportToArray());
    }

    /**
     * @param string $data
     */
    public function unserialize($data)
    {
        $this->__construct();
        $data = json_decode($data, true);
        $this->importFromArray($data);
    }
}
