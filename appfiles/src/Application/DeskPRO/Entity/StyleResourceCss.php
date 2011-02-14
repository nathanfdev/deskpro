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
use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Web;

/**
 * CSS resource
 *
 * @orm:Entity
 * @orm:Table(name="style_resource_css")
 */
class StyleResourceCss extends StyleResource
{
	/**
	 * @return string
	 */
	public function getFilename()
	{
		return parent::getFilename() . '.css';
	}



	/**
	 * @return array
	 */
	public function getResourceHeaders()
	{
		return Web::getAttachmentHeaders(
			$this->getFilename(),
			true,
			'text/css',
			strlen($this->resource)
		);
	}


	/**
	 * @orm:PreUpdate
	 */
	public function updateCompiledCss()
	{
		if (!(
			$this->hasPropertyChanged('raw_resource') OR
			$this->hasPropertyChanged('user_data')
		)) {
			return;
		}

		$compiled_css = $this->raw_resource;

		#-------------------------
		# Read tags in raw source into variables:
		# <var:color name="whatever" value="#fff" description="BG color">
		#-------------------------

		$regex = '#<var:([a-z]+)\s+name="(?:[^"\\]|\\.)*"\s+value="(?:[^"\\]|\\.)*">#';
		$matches = null;
		if (!preg_match_all($regex, $this->raw_resource, $matches, PREG_PATTERN_ORDER)) {
			$matches = array();
		}

		$vars = array();
		foreach ($matches as $match) {
			$type  = $match[1];
			$name  = $match[2];
			$value = $match[3];

			$vars[$name] = array(
				'type' => $type,
				'value' => $value,
			);

			// The vartag itself becomes an insert tag now that we have read in the metadata
			$compiled_css = str_replace($match[0], '<insert:'.$name.'>', $compiled_css);
		}

		$this->resource_data = $vars;

		#-------------------------
		# Process replacement values using default values or user data
		# <insert:somename>
		#-------------------------

		$regex = '#<insert:(.*?)>#';
		$matches = null;
		if (!preg_match_all($regex, $compiled_css, $matches, PREG_PATTERN_ORDER)) {
			$matches = array();
		}

		foreach ($matches as $match) {
			$varname = $match[1];
			$value = '';

			if (isset($this->user_data[$varname])) {
				$value = $this->user_data[$varname];
			} elseif (isset($this->resource_data[$varname]['value'])) {
				$value = $this->resource_data[$varname];
			}

			$compiled_css = str_replace($match[0], $value, $compiled_css);
		}

		$this->resource = $compiled_css;
	}
}