<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
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
		$download->content = $this->content;
		$download->slug = $this->slug;
		$download->setStatusCode($this->status);

		$cat = $this->_em->find('DeskPRO:DownloadCategory', $this->category_id);
		$download->category = $cat;

		$download->getLabelManager()->setLabelsArray($this->labels);

        $blob = App::getOrm()->getRepository('DeskPRO:Blob')->find($this->attach);
        $download->blob = $blob;

		$this->_em->persist($download);
		$this->_em->flush();
		$this->_em->commit();

		$this->_download = $download;
	}

	public function getDownload()
	{
		return $this->_download;
	}
}
