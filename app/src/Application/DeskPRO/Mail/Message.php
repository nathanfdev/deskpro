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
 * @subpackage
 */

namespace Application\DeskPRO\Mail;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class Message extends \Orb\Mail\Message
{
	/**
	 * @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface
	 */
	protected $template_engine;

	/**
	 * @var string
	 */
	protected $template;

	/**
	 * @var array
	 */
	protected $template_vars;


	public function doPrepare()
	{
		if ($this->template) {
			$content = $this->template_engine->render($this->template, $this->template_vars);
			if (strpos($content, '___DP___SUBJECT___SEP___') !== false) {
				list ($subject, $body) = explode('___DP___SUBJECT___SEP___', $content, 2);

				// Try to clean up subject from whitespace
				$subject = \Orb\Util\Strings::removeEmptyLines($subject);
				$subject = \Orb\Util\Strings::trimLines($subject);
				$subject = str_replace(array("\r\n", "\n"), ' ', $subject);
				$subject = trim($subject);

				$body = trim($body);
			} else {
				$subject = '';
				$body = $content;
			}

			if ($subject) {
				$this->setSubject($subject);
			}

			$this->setBody($body, 'text/html');
		}

		// These need to be unset so the message can be properly serialized
		// if it needs to be inserted as a queued message
		$this->template        = null;
		$this->template_vars   = null;
		$this->template_engine = null;
	}


	/**
	 * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
	 */
	public function setTemplateEngine(EngineInterface $template_engine)
	{
		$this->template_engine = $template_engine;
	}


	/**
	 * Set the template we'll use to fetch the subject and body from
	 *
	 * @param $name
	 * @param array $vars
	 */
	public function setTemplate($name, array $vars = array())
	{
		$this->template = $name;
		$this->template_vars = $vars;
	}

	/**
	 * @static
	 * @param null $subject
	 * @param null $body
	 * @param null $contentType
	 * @param null $charset
	 * @return \Application\DeskPRO\Mail\Message
	 */
	public static function newInstance($subject = null, $body = null, $contentType = null, $charset = null)
	{
		return new static($subject, $body, $contentType, $charset);
	}
}