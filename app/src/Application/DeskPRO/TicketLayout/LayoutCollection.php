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
 * @category Entities
 */

namespace Application\DeskPRO\TicketLayout;

use Orb\Util\Strings;

class LayoutCollection implements \Countable, \IteratorAggregate
{
	/**
	 * Array of layouts keyed by some unique key
	 *
	 * @var Layout[]
	 */
	private $layouts;

	/**
	 * Adds a layout to the collection
	 *
	 * @param Layout $layout   The layout to add
	 * @param string $key      The layout key, or null
	 * @throws \OutOfBoundsException
	 */
	public function addLayout($layout, $key)
	{
		if (isset($this->layouts[$key])) {
			throw new \OutOfBoundsException();
		}

		if ($key === null) {
			$key = '0';
		}

		$this->layouts[$key] = $layout;
	}


	/**
	 * @param string $key
	 * @return bool
	 */
	public function hasLayout($key)
	{
		return isset($this->layouts[$key]);
	}


	/**
	 * @return bool
	 */
	public function hasDefaultLayout()
	{
		return isset($this->layouts[0]);
	}


	/**
	 * @param string $key
	 * @return Layout
	 * @throws \InvalidArgumentException
	 */
	public function getLayout($key)
	{
		if (isset($this->layouts[$key])) {
			return $this->layouts[$key];
		} else if (isset($this->layouts[0])) {
			return $this->layouts[0];
		}

		throw new \InvalidArgumentException("No layout exists");
	}


	/**
	 * @return Layout
	 * @throws \InvalidArgumentException
	 */
	public function getDefaultLayout()
	{
		if (!isset($this->layouts[0])) {
			throw new \InvalidArgumentException("No default layout exists");
		}

		return $this->layouts[0];
	}


	/**
	 * @return string
	 */
	public function compileJsObj()
	{
		$js = "(function() {\n";
		$js .= "\tvar layoutMap = {\n";

		$layout_codes = array();
		foreach ($this->layouts as $k => $layout) {
			$k_str = "'$k'";

			$code = trim(Strings::modifyLines($layout->compileJsObj(), "\t\t\t"));
			$bit_js ="\t\t{$k_str}: {$code}";
			$layout_codes[] = $bit_js;
		}

		$js .= implode(",\n", $layout_codes) . "\n";
		$js .= "\t};\n";

		$js .= "\treturn {\n";
		$js .= "\t\tgetLayout: function(id) {\n";
		$js .= "\t\t\treturn layoutMap[id+''] || layoutMap['0'] || null;\n";
		$js .= "\t\t}\n";
		$js .= "\t};\n";

		$js .= "})()";

		return $js;
	}


	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->layouts);
	}


	/**
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->layouts);
	}
}