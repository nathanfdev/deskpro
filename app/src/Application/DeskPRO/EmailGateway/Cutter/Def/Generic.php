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
 */

namespace Application\DeskPRO\EmailGateway\Cutter\Def;

use Orb\Util\Strings;

class Generic implements ForwardDef, QuoteDef
{
	/**
	 * Tries to split a message by looking at the first sequence of From/To/Date/Subject headers
	 * to mark the beginning.
	 *
	 * @param $body
	 * @return array|null
	 */
	public function splitFromFirstHeaderText($body)
	{
		$body = Strings::standardEol($body);
		$body = explode("\n", $body);

		$found = 0;
		$start_line = null;

		foreach ($body as $ln => $l) {
			$l = preg_replace('#^\s*>+\s*#', '', $l);
			if (preg_match('#^(From|To|Date|Subject):(.*?)$#i', $l)) {
				if (!$start_line) {
					$start_line = $ln;
				}
				$found++;
				if ($found >= 2) break;
			} else {
				$found = 0;
				$start_line = null;
			}
		}

		// If we didnt find at least two of the four headers,
		// consider it a no-match
		if ($found < 2) {
			return null;
		}

		$parts = array(
			array_slice($body, 0, $start_line),
			array_slice($body, $start_line),
		);

		$parts[0] = implode("\n", $parts[0]);
		$parts[1] = implode("\n", $parts[1]);

		return $parts;
	}


	/**
	 * Get an array of info from the forwarded block
	 *
	 * @param string $body
	 * @param bool $is_html
	 * @return array
	 */
	public function getForwardInfo($body, $is_html = false)
	{
		$forward_data = array(
			'message_body'         => null,
			'fwd_message_body'     => null,
			'fwd_message_headers'  => null,
			'fwd_from_email'       => null,
			'fwd_from_name'        => null,
		);

		$parts = preg_split('#-{3,15}\s*Forward(ed)?( Message)?\s*-{3,15}#i', $body, 2);
		if (!$parts || count($parts) != 2) {
			$parts = preg_split('#^\s*Forward(ed)?( Message):\s*$#im', $body, 2);
			if (!$parts || count($parts) != 2) {
				$parts = $this->splitFromFirstHeaderText($body);
				if (!$parts || count($parts) != 2) {
					return $forward_data;
				}
			}
		}

		$forward_data['message_body'] = $parts[0];

		#------------------------------
		# Split the forwarded message into
		# a header section and a body
		#------------------------------

		// Dequote
		$fwd_message_body = explode("\n", Strings::standardEol($parts[1]));
		foreach ($fwd_message_body as &$l) {
			$l = preg_replace('#^\s*>+\s*#', '', $l);
			$l = trim($l);
		}
		unset($l);

		$fwd_message_body = implode("\n", $fwd_message_body);

		if ($is_html) {
			$fwd_message_body = str_replace(array('<br />', '<br/>'), '<br>', $fwd_message_body);
			$fwd_parts = preg_split('#<br>\s*<br>#i', $fwd_message_body, 2);
		} else {
			$fwd_parts = preg_split('#\n{2}#i', $fwd_message_body, 2);
		}

		if (count($fwd_parts) != 2) {
			return $forward_data;
		}

		$forward_data['fwd_message_headers'] = $fwd_parts[0];
		$forward_data['fwd_message_body']    = $fwd_parts[1];

		#------------------------------
		# Try to read the email address from the fwd headers
		#------------------------------

		$pos = stripos($forward_data['fwd_message_headers'], 'from');
		if (!$pos) {
			return $forward_data;
		}

		$from_str = substr($forward_data['fwd_message_headers'], $pos);
		$m = null;

		// From: Name <email@tdl.com>
		if (preg_match('#From:\s*(.*?)\s*<(.*?)@(.*?)>#i', $from_str, $m)) {
			$forward_data['fwd_from_name'] = $m[1];
			$forward_data['fwd_from_email'] = $m[2] . '@' . $m[3];

		// From: email@tdl.com
		} elseif (preg_match('#From:\s*<?(.*?)@(.*?)>?#i', $from_str, $m)) {
			$forward_data['fwd_from_email'] = $m[1] . '@' . $m[2];

		// Try to find any email address on the line,
		// we've cut $from_str to be after 'From' so the first match sholud be
		// the email we want
		} elseif (preg_match('#\s(.*?)@(.*?)\s#i', $from_str, $m)) {
			$forward_data['fwd_from_email'] = $m[1] . '@' . $m[2];
		}

		return $forward_data;
	}


	/**
	 * Cut out the quote block
	 *
	 * @param string $body
	 * @param bool $is_html
	 * @return string
	 */
	public function cutQuoteBlock($body, $is_html = false)
	{
		if (($pos = strpos($body, '<!--DP_TOP_MARK-->')) === false) {
			if (($pos = strpos($body, '_______________________.')) === false) {
				return $body;
			}
		}

		$body = substr($body, 0, $pos);

		// We try to cut known markers only when the top marker was successfully found
		$body = $this->cutKnownQuoteMarkers($body, $is_html);

		return $body;
	}


	/**
	 * @param $body
	 * @param $is_html
	 */
	public function cutKnownQuoteMarkers($body, $is_html)
	{
		// On Wednesday, 25 April 2012 at 17:18, John Doe wrote:
		$parts = preg_split('#(<p>)?\s*On [a-zA-Z]+, [0-9]+ [a-zA-Z]+? [0-9]+ at [0-9]+:[0-9]+\s*(AM|am|PM|pm)?, .*? wrote:\s*(</p>)?#', $body, 2);
		if (isset($parts[1])) {
			return $parts[0];
		}

		// On 24/04/2012 17:12, John Doe wrote:
		$parts = preg_split('#(<p>)?\s*On [0-9]{2}(/|\-)[0-9]{2}(/|\-)[0-9]{2,4} [0-9]{1,2}:[0-9]{1,2}( AM| am| PM| pm)?, .*? wrote:\s*(</p>)?#', $body, 2);
		if (isset($parts[1])) {
			return $parts[0];
		}

		// -----Original Message-----
		// From: John Doe
		$parts = preg_split("#(>*\s*)?-----Original Message-----\n(>*\s*)?From:\s#", $body, 2);
		if (isset($parts[1])) {
			return $parts[0];
		}

		// From: John Doe
		// Sent: 30 April 2012 08:24
		// To: Jane Doe
		// Subject: ABC
		if (!$is_html) {
			$parts = preg_split("#From: .*?\nSent: .*?\nTo: .*?\nSubject: .*?#", $body, 2);
			if (isset($parts[1])) {
				return $parts[0];
			}
		} else {
			// Standard formatting with outlook
			$parts = preg_split("#<b><span.*?>From:\s*</span></b>\s*<span.*?>.*?</span>\s*(<br>|<br/>|<br />)\n?<b><span.*?>Sent:\s*</span></b>\s*<span.*?>.*?</span>\s*(<br>|<br/>|<br />)\n?<b><span.*?>To:\s*</span></b>\s*<span.*?>.*?</span>\s*(<br>|<br/>|<br />)\n?<b><span.*?>Subject:\s*</span></b>\s*<span.*?>.*?</span>\s*(<br>|<br/>|<br />)#", $body, 2);
			if (isset($parts[1])) {
				return $parts[0];
			}
		}

		return $body;
	}
}
