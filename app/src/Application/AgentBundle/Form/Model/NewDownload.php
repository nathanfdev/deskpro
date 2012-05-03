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
 * @subpackage AgentBundle
 */

namespace Application\AgentBundle\Form\Model;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Person;

class NewDownload
{
	public $title;
	public $category_id;
	public $status;
	public $content;

	public $slug;
	public $labels_json;
	public $labels = array();
	public $attach = null;

	protected $_download;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $_em;

	public function __construct(Person $person_context)
	{
		$this->_person_context = $person_context;

		$this->_em = App::getOrm();
	}

	public function save()
	{
		$this->_em->beginTransaction();

		$download = new Download();
		$download->person = $this->_person_context;
		$download->title = $this->title;
		$download->content = $this->content ?: '';
		$download->slug = $this->slug;
		$download->setStatusCode($this->status);

		$cat = $this->_em->find('DeskPRO:DownloadCategory', $this->category_id);
		$download->category = $cat;

		$blob = App::getOrm()->getRepository('DeskPRO:Blob')->find($this->attach);
		$download->blob = $blob;
		$this->_em->persist($download);

		if ($this->labels_json) {
			$this->labels = @json_decode($this->labels_json);
			if (!is_array($this->labels)) {
				$this->labels = array();
			}
		}

		if ($this->labels) {
			$download->getLabelManager()->setLabelsArray($this->labels);
			$this->_em->flush();
		}

		$this->_em->commit();

		$this->_download = $download;
	}

	public function getDownload()
	{
		return $this->_download;
	}
}
