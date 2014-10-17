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

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Templating
 */

namespace Application\DeskPRO\Templating;

use Application\DeskPRO\Translate\Translate;

class EmailTemplatesDesc
{
	/**
	 * @var array
	 */
	private $manifest;

	/**
	 * @param null      $manifest_path
	 */
	public function __construct($manifest_path = null)
	{
		if ($manifest_path === null) {
			$manifest_path = DP_ROOT.'/src/Application/DeskPRO/Resources/views/config/email-tpls.php';
		}

		$this->manifest = require($manifest_path);
	}

	/**
	 * @return array
	 */
	public function getManifest()
	{
		return $this->manifest;
	}


	/**
	 * @param Translate $tr
	 * @return array
	 */
	public function getManifestWithDescriptions(Translate $tr)
	{
		$ret = array();

		foreach ($this->manifest as $tpl) {
			$ret[] = $this->getTplDisplayInfo($tpl, $tr);
		}

		return $ret;
	}


	/**
	 * @param array $tpl
	 * @param Translate $tr
	 * @return string
	 */
	public function getTplDisplayInfo(array $tpl, Translate $tr)
	{
		$title_id = $this->_getTplPhraseId($tpl['name']) . '_title';
		$desc_id  = $this->_getTplPhraseId($tpl['name']) . '_desc';

		$show_name = $tpl['name'];
		$show_name = str_replace('DeskPRO:', '', $show_name);
		$show_name = str_replace(':', '/', $show_name);
		$show_name = str_replace('.twig', '', $show_name);
		$tpl['showName'] = $show_name;

		$tpl['title'] = $tr->hasPhrase($title_id) ? $tr->phrase($title_id) : $title_id;
		$tpl['desc']  = $tr->hasPhrase($desc_id) ?  $tr->phrase($desc_id)  : $desc_id;

		return $tpl;
	}


	/**
	 * Gets a list of tempaltes grouped by type and group, with translated titles and descriptions.
	 *
	 * @param Translate $tr
	 * @return array
	 */
	public function getProcessedList(Translate $tr)
	{
		$manifest = $this->getManifestWithDescriptions($tr);

		$ret = array();

		foreach ($manifest as $tpl) {
			$type  = $tpl['typeId'];
			$group = $tpl['groupId'];

			if (!isset($ret[$type])) {
				$ret[$type] = array(
					'typeId' => $type,
					'title'  => $tr->phrase("adm.email_templates.$type"),
					'groups' => array()
				);
			}
			if (!isset($ret[$type]['groups'][$group])) {
				$ret[$type]['groups'][$group] = array(
					'groupId'   => $group,
					'title'     => $tr->phrase("adm.email_templates.{$type}_{$group}"),
					'templates' => array()
				);
			}

			$ret[$type]['groups'][$group]['templates'][] = $tpl;
		}

		return $ret;
	}


	/**
	 * @param string $name
	 * @return string
	 */
	private function _getTplPhraseId($name)
	{
		$name = str_replace('DeskPRO:', '', $name);
		$name = str_replace('.', '-', $name);
		$name = str_replace(':', '_', $name);
		$name = str_replace('-twig', '', $name);
		$name = str_replace('-html', '', $name);

		return 'adm.email_templates.' . $name;
	}
}