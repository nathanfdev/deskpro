<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Log
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Log\Writer;
use \Orb\Log\LogItem;


/**
 * This writer writes to any stream
 */
class Stream extends AbstractWriter
{
 /**
	 * Holds the PHP stream to log to.
	 * @var null|stream
	 */
	protected $_stream = null;

	/**
	 * If we opened the stream ourselves
	 * @var bool
	 */
	protected $_did_open_stream = false;

	/**
	 * @param  mixed  streamOrUrl     Stream or URL to open as a stream
	 * @param  string mode            Mode, only applicable if a URL is given
	 */
	public function __construct($stream_or_url, $mode = null, $add_lineformatter = true)
	{
		// Setting the default
		if ($mode === null) {
			$mode = 'a';
		}

		if (is_resource($stream_or_url)) {
			if (get_resource_type($stream_or_url) != 'stream') {
				throw new \InvalidArgumentException('Resource is not a stream');
			}

			if ($mode != 'a') {
				throw new \InvalidArgumentException('Mode cannot be changed on existing streams');
			}

			$this->_stream = $stream_or_url;
		} else {
			if (!($this->_stream = @fopen($stream_or_url, $mode, false))) {
				$msg = "\"$stream_or_url\" cannot be opened with mode \"$mode\"";
				throw new \RuntimeException($msg);
			}

			$this->_did_open_stream = true;
		}

		if ($add_lineformatter) {
			$this->addFilter(new \Orb\Log\Filter\SimpleLineFormatter());
		}
	}


	public function shutdown()
	{
		if ($this->_did_open_stream AND is_resource($this->_stream)) {
			fclose($this->_stream);
		}
	}

	/**
	 * Write a message to the log.
	 */
	public function _write(LogItem $log_item)
	{
		if (false === @fwrite($this->_stream, $log_item[LogItem::MESSAGE_LINE] . "\n")) {
			throw new \RuntimeException("Unable to write to stream");
		}
	}
}
