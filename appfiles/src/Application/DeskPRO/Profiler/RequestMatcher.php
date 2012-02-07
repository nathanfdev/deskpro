<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Profiler
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Profiler;

use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class RequestMatcher implements RequestMatcherInterface
{
	public function matches(Request $request)
	{
		if (isset($GLOBALS['DP_CONFIG']['debug']['enable_profiler']) && $GLOBALS['DP_CONFIG']['debug']['enable_profiler']) {
			return true;
		}

		return false;
	}
}
