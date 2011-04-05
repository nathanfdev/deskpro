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

use \Application\DeskPRO\App;

use \Doctrine\ORM\EntityRepository;

class CustomDefTicket extends EntityRepository
{
	protected $_fields = null;

	protected function _initFields()
	{
		if ($this->_fields !== null) return;
		$this->_fields = array();

		if (true /*($this->_fields = App::getCache('common')->load('custom_def_ticket_fields')) === false*/) {
			$all_fields = $this->findAll();

			foreach ($all_fields as $f) {
				if (!$f['parent']) {
					$this->_fields[] = $f;
				}
			}

			//App::getCache('common')->save($this->_fields, null, array('custom_def_ticket_fields'));
		}

		return $this->_fields;
	}


	/**
	 * @return array
	 */
	public function getFields()
	{
		$this->_initFields();
		return $this->_fields;
	}
}