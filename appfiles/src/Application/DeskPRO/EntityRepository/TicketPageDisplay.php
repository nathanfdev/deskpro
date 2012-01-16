<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EntityRepository;


use Application\DeskPRO\App;
use Application\DeskPRO\Entity\TicketPageDisplay as TicketPageDisplayEntity;
use Doctrine\ORM\EntityRepository;

class TicketPageDisplay extends EntityRepository
{
	public function getFromZone($zone, $department_context = null)
	{
		if ($department_context) {
			if (is_object($department_context)) {
				$department_context = $department_context['id'];
			}

			return $this->getEntityManager()->createQuery("
				SELECT d
				FROM DeskPRO:TicketPageDisplay d
				WHERE d.zone = :zone AND d.department = :department
			")->setParameters(array('zone' => $zone, 'department' => $department_context))->execute();
		} else {
			return $this->getEntityManager()->createQuery("
				SELECT d
				FROM DeskPRO:TicketPageDisplay d
				WHERE d.zone = :zone
			")->setParameters(array('zone' => $zone))->execute();
		}
	}

	public function getSection($department, $zone, $section)
	{
		try {
			$d = $this->findOneBy(array('department' => $department ? $department['id'] : null, 'zone' => $zone, 'section' => $section));
			return $d;
		} catch (\Exception $e) {
			return null;
		}
	}

	public function getSectionData($department, $zone, $section)
	{
		if ($department === null) {
			$data = App::getDb()->fetchColumn("
				SELECT data
				FROM ticket_page_display
				WHERE department_id IS NULL AND zone = ? AND section = ?
			", array($zone, $section));
		} else {
			if (is_array($department) || is_object($department)) {
				$department = $department['id'];
			}
			$data = App::getDb()->fetchColumn("
				SELECT data
				FROM ticket_page_display
				WHERE department_id = ? AND zone = ? AND section = ?
			", array($department, $zone, $section));
		}

		if (!$data) {
			return null;
		}

		if ($data) {
			$data = unserialize($data);
		}

		if (!$data) {
			return array();
		}

		return $data;
	}

	public function getOrCreate($department, $zone, $section)
	{
		$d = null;
		try {
			$d = $this->findOneBy(array('department' => $department ? $department['id'] : null, 'zone' => $zone, 'section' => $section));
		} catch (\Exception $e) {}

		if (!$d) {
			$d = new TicketPageDisplayEntity();
			$d->department = $department;
			$d['zone'] = $zone;
			$d['section'] = $section;
		}

		return $d;
	}
}
