<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Elastica
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Elastica\Type;

use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Elastica\Transformer\DownloadTransformer;

use APplication\DeskPRO\App;

/**
 * Type for the Download entity
 */
class DownloadType extends AbstractType
{
	/**
	 * Transform a value into a Document
	 *
	 * @param  Download $download
	 * @return \Elastic_Document
	 */
	public function transformToDocument($download)
	{
		$trans = new DownloadTransformer();
		$doc = $trans->transform($download);

		$doc->setIndex('content');
		$doc->setType('download');

		return $doc;
	}


	/**
	 * Get a single value from a document
	 *
	 * @return \Application\DeskPRO\Entity\Download
	 */
	protected function getValueFromResult(\Elastica_Result $doc)
	{
		return App::getEntityRepository('DeskPRO:Download')->find($doc->getId());
	}
}