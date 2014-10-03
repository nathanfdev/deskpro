<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

namespace Application\DeskPRO\NewSearch\SearchEngine\Elastic;

use Doctrine\ORM\EntityManager;

class ElasticaResultsTransformer
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @param EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}


	/**
	 * @param \Elastica\Result[] $results
	 * @return array
	 */
	public function transform(array $results)
	{
		#------------------------------
		# Sort results by type so we can fetch
		# results from db in one go
		#------------------------------

		$ent_ids = array();
		foreach ($results as $hit) {
			$ent = $this->getEntityFromType($hit->getType());
			if (!isset($ent_ids[$ent])) {
				$ent_ids[$ent] = array();
			}

			$ent_ids[$ent][] = $hit->getId();
		}

		#------------------------------
		# Fetch results from db
		#------------------------------

		$objects = array();
		foreach ($ent_ids as $ent => $ids) {
			$ent_objects = $this->em->getRepository($ent)->getByIds($ids, true);
			if ($ent_objects) {
				foreach ($ent_objects as $o) {
					$key = $ent . ':' . $o->id;
					$objects[$key] = $o;
				}
			}
		}

		#------------------------------
		# Finally sort into one main array
		#------------------------------

		$sorted_objects = array();
		foreach ($results as $hit) {
			$ent = $this->getEntityFromType($hit->getType());
			$key = $ent . ':' . $hit->getId();
			if (isset($objects[$key])) {
				$sorted_objects[] = $objects[$key];
			}
		}

		return $sorted_objects;
	}


	/**
	 * @param string $type
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	private function getEntityFromType($type)
	{
		//todo this should be generalised somewhere

		switch ($type) {
			case 'article':           return 'DeskPRO:Article';
			case 'download':          return 'DeskPRO:Download';
			case 'news':              return 'DeskPRO:News';
			case 'feedback':          return 'DeskPRO:Feedback';
			case 'chat_conversation': return 'DeskPRO:ChatConversation';
			case 'person':            return 'DeskPRO:Person';
			case 'ticket':            return 'DeskPRO:Ticket';
			default:
				throw new \InvalidArgumentException();
		}
	}
}