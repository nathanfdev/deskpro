<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category ORM
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Labels;

use Application\DeskPRO\App;
use Orb\Util\Strings;
use Orb\Util\Arrays;

class ContentLabelCloud
{
	protected $cloud = null;
	
	public function getCloud()
	{
		if ($this->cloud !== null) return $this->cloud;

		$counts = array(
			'articles'     => App::getEntityRepository('DeskPRO:LabelDef')->getLabelCounts('articles', 25),
			'feedback'        => App::getEntityRepository('DeskPRO:LabelDef')->getLabelCounts('feedback', 25),
			'downloads'    => App::getEntityRepository('DeskPRO:LabelDef')->getLabelCounts('downloads', 25),
			'news'         => App::getEntityRepository('DeskPRO:LabelDef')->getLabelCounts('news', 25),
		);

		$label_counts = array();
		foreach ($counts as $type_counts) {
			foreach ($type_counts as $label => $count) {
				if (!isset($label_counts[$label])) $label_counts[$label] = 0;
				$label_counts[$label] += $count;
			}
		}

		asort($label_counts, SORT_NUMERIC);
		if (count($label_counts) > 25) {
			$label_counts = Arrays::spliceAssoc($label_counts, 0, 25);
		}

		$cloud_gen = new \Application\DeskPRO\UI\TagCloud($label_counts);
		$this->cloud = $cloud_gen->getCloud();

		return $this->cloud;
	}
}