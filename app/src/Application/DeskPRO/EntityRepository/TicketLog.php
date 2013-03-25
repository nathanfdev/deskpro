<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
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

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use \Doctrine\ORM\EntityRepository;

class TicketLog extends AbstractEntityRepository
{
	public function getLogsForTicket(Entity\Ticket $ticket, array $options = array())
	{
		if (!isset($options['order_dir'])) {
			$options['order_dir'] = 'ASC';
		}

		if (!empty($options['since_id'])) {
			$query = $this->_em->createQuery("
				SELECT log
				FROM DeskPRO:TicketLog log INDEX BY log.id
				WHERE log.ticket = ?1 AND log.id > ?2
				ORDER BY log.date_created {$options['order_dir']}
			")->setParameter(1, $ticket)->setParameter(2, $options['since_id']);
		} else {
			$query = $this->_em->createQuery("
				SELECT log
				FROM DeskPRO:TicketLog log INDEX BY log.id
				WHERE log.ticket = ?1
				ORDER BY log.date_created ASC
			")->setParameter(1, $ticket);
		}

		return $query->execute();
	}

    public function getLogsForAgent(Entity\Person $agent, array $options = array())
    {
        if(isset($options['date_range'])) {
            $query = $this->_em->createQuery("
				SELECT log
				FROM DeskPRO:TicketLog log INDEX BY log.id
				WHERE log.person = ?1
				AND log.date_created BETWEEN ?2 AND ?3
				ORDER BY log.date_created ASC
			")
            ->setParameter(1, $agent)
            ->setParameter(2, $options['date_range']['start'])
            ->setParameter(3, $options['date_range']['end'])
            ;
        } else {
            $query = $this->_em->createQuery("
				SELECT log
				FROM DeskPRO:TicketLog log INDEX BY log.id
				WHERE log.person = ?1
				ORDER BY log.date_created ASC
			")
            ->setParameter(1, $agent);
        }

        return $query->execute();
    }
}
