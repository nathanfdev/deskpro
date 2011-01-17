<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Twig
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Twig\Loader;

use Symfony\Component\Templating\Engine;
use Symfony\Component\Templating\Storage\Storage;
use Symfony\Component\Templating\Storage\FileStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Application\DeskPRO\Entities;

use Orb\Arrays;
use Orb\Strings;

/**
 * This hybrid loader loads templates from the filesystem first, and then from the
 * database second if a style is being used and has templates that override it.
 */
class Hybrid extends \Symfony\Bundle\TwigBundle\Loader\Loader
{
	/**
	 * The current database-stored style being used
	 * @var Style
	 */
	protected $style = null;

	/**
	 * An array of id=>array(info). This is an array of all templates specified in
	 * a style and their last updated date. We use this to sort out which templates
	 * to use, or if we should read it from the filesystem.
	 *
	 * @var array
	 */
	protected $style_template_info = array();

	/**
	 * The database connection we'll use to fetch templates. Not using ORM, faster
	 * to fetch with pure sql.
	 *
	 * @var Doctrine\DBAL\Connection
	 */
	protected $dbconn = null;



	/**
	 * Set the database connection to use
	 * @param Doctrine\DBAL\Connection $dbconn
	 */
	public function setDb($dbconn)
	{
		$this->dbconn = $dbconn;
	}



	/**
	 * Set the database-based style we should try to use.
	 *
	 * @param Style $style
	 */
	public function setStyle(Style $style)
	{
		$this->style = $style;

		// TODO sort out parent/child hierarchy stuff
		if ($this->dbconn) {
			$this->style_template_info = $this->dbconn->fetchAll('SELECT id, path, updated_at FROM templates WHERE style_id = ?', array($this->style['id']));
			$this->style_template_info = Arrays::keyFromData('path');
		}
	}



    /**
     * Gets the source code of a template, given its name.
     *
     * @param  string $name string The name of the template to load
     *
     * @return string The template source code
     */
    public function getSource($name)
    {
        if ($name instanceof Storage) {
            return $name->getContent();
        }

		if (isset($this->style_template_info[$name])) {
			$tplinfo = $this->style_template_info[$name];
			return $this->dbconn->fetchColumn('SELECT template FROM templates WHERE id = ?', array($tplinfo['id']));
		}

        list($name, $options) = $this->engine->splitTemplateName($name, array('renderer' => 'twig'));

        $template = $this->engine->getLoader()->load($name, $options);

        if (false === $template) {
            throw new \InvalidArgumentException(sprintf('The template "%s" does not exist (renderer: %s).', $name, $options['renderer']));
        }

        return $template->getContent();
    }



    /**
     * Gets the cache key to use for the cache for a given template name.
     *
     * @param  string $name string The name of the template to load
     *
     * @return string The cache key
     */
    public function getCacheKey($name)
    {
        if ($name instanceof Storage) {
            return (string) $name;
        }

		if (isset($this->style_template_info[$name])) {
			$tplinfo = $this->style_template_info[$name];
			return $this->style['id'] . '_' . $tplinfo['id'];
		}

        list($name, $options) = $this->engine->splitTemplateName($name, array('renderer' => 'twig'));

        return $name.'_'.serialize($options);
    }



    /**
     * Returns true if the template is still fresh.
     *
     * @param string    $name The template name
     * @param timestamp $time The last modification time of the cached template
     */
    public function isFresh($name, $time)
    {
        if ($name instanceof Storage) {
            if ($name instanceof FileStorage) {
                return filemtime((string) $name) < $time;
            }

            return false;
        }

		if (isset($this->style_template_info[$name])) {
			$tplinfo = $this->style_template_info[$name];
			$date = new \DateTime($tplinfo['updated_at']);

			return ($date->getTimestamp() < $time);
		}

        list($name, $options) = $this->engine->splitTemplateName($name, array('renderer' => 'twig'));

        return $this->engine->getLoader()->isFresh($name, $options, $time);
    }
}
