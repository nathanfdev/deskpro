<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Escalations;

use Application\DeskPRO\Criteria\CriteriaTermInterface;
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
     * @param  FilterTermInterface       $term
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
     * @param  array                     $term_info
     * @throws \InvalidArgumentException
     */
    public function addTermFromArray(array $term_info)
    {
        $class_name = "Application\\DeskPRO\\Tickets\\Filters\\Terms\\{$term_info['type']}";
        if (!class_exists($class_name)) {
            throw new \InvalidArgumentException("Unknown term {$term_info['type']} (could not locate class: $class_name)");
        }

        $term = new $class_name($term_info['op'], $term_info['options']);
        $this->addTerm($term);
    }


    /**
     * {@inheritDoc}
     */
    public function getFilterQuery()
    {
        return $this->criteria->getFilterQuery();
    }


    /**
     * @return array
     */
    public function exportToArray()
    {
        $data = array();

        $data['version']  = 1;
        $data['terms'] = array();
        foreach ($this->criteria->getAll() as $criteria) {
            if (!($criteria instanceof CriteriaTermInterface)) {
                continue;
            }

            $data['terms'][] = array(
                'type'    => $criteria->getTermType(),
                'op'      => $criteria->getTermOperator(),
                'options' => $criteria->getTermOptions()
            );
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
