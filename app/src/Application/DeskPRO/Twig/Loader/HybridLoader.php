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
 * @category Twig
 */

namespace Application\DeskPRO\Twig\Loader;

use Application\DeskPRO\App;
use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Component\Config\FileLocatorInterface;

/**
 * This hybrid loader loads templates from the filesystem first, and then from the
 * database second if a style is being used and has templates that override it.
 */
class HybridLoader extends \Symfony\Bundle\TwigBundle\Loader\FilesystemLoader
{
	protected static $inst_count = 0;
	/**
	 * @var \Application\DeskPRO\Entity\Style
	 */
	protected $style = null;

	/**
	 * @var array
	 */
	protected $style_template_info = null;

	/**
	 * @var string
	 */
	protected $db_path_prefix;

	public function __construct(FileLocatorInterface $locator, TemplateNameParserInterface $parser)
	{
		parent::__construct($locator, $parser);

		self::$inst_count++;
		$stream = 'tpl' . self::$inst_count;
		$this->db_path_prefix = $stream . '://load';

		stream_wrapper_register($stream, 'Application\\DeskPRO\\Twig\\Loader\\DbStreamWrapper', 0);
	}

	protected function _initStyle()
	{
		// Already done
		if ($this->style !== null) return;

		if (!defined('DP_BUILDING')) {
			$this->style = App::getSystemService('style');

			if (App::getConfig('debug.templates.disable_db_templates')) {
				$this->style_template_info = App::getDb()->fetchAllKeyed("
					SELECT id, name, UNIX_TIMESTAMP(date_updated) AS date_updated
					FROM templates
					WHERE style_id = ?
				", array($this->style['id']), 'name');
			}
		} else {
			$this->style = new \Application\DeskPRO\Entity\Style();
		}
	}

	public function isFresh($name, $time)
    {
		$this->_initStyle();

		$str_name = $this->_getStringName($name);

		// DB templates are always "fresh" because theyre compiled
		// as soon as they're saved
		if (isset($this->style_template_info[$str_name])) {
			return true;
		}

        return parent::isFresh($name, $time);
    }

	public function getCacheKey($name)
    {
		$this->_initStyle();

		$str_name = $this->_getStringName($name);
		if (isset($this->style_template_info[$str_name])) {
			return md5($this->style['id'] . '_' . $str_name);
		} else {
			return md5($str_name);
		}
    }

	public function getSource($name)
    {
		$this->_initStyle();

		$str_name = $this->_getStringName($name);
		if (isset($this->style_template_info[$str_name])) {
			return App::getDb()->fetchColumn("
				SELECT template_code
				FROM templates
				WHERE id = ?
			", array($this->style_template_info[$name]['id']));
		}

		return parent::getSource($name);
    }

	protected function findTemplate($template)
	{
		$this->_initStyle();

		$logicalName = (string)$template;

		if (isset($this->style_template_info[$logicalName])) {
			return $this->db_path_prefix . '/' . $logicalName;
		}

		return parent::findTemplate($template);
	}

	protected function _getStringName($tpl)
	{
		if (is_string($tpl)) return $tpl;

		$info = $tpl->all();

		$str_name = "{$info['bundle']}:{$info['controller']}:{$info['name']}.{$info['format']}.{$info['engine']}";
		return $str_name;
	}
}
