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

namespace Application\DeskPRO\Entity;

/**
 * Labels on downloads
 *
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="labels_downloads")
 */
class LabelDownload extends LabelAssocAbstract
{
	const LABEL_TYPENAME = 'downloads';

	/**
	 * @var \Application\DeskPRO\Entity\Article
	 * @orm:Id
	 * @orm:ManyToOne(targetEntity="Download")
	 * @orm:JoinColumn(name="download_id", referencedColumnName="id")
	 */
	protected $download;
}