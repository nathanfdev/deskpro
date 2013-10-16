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

namespace Application\DeskPRO\Templating\Templates;

abstract class Template
{
	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var \Application\DeskPRO\Templating\Templates\TemplateCode
	 */
	private $template_code;

	/**
	 * @var \Application\DeskPRO\Templating\Templates\TemplateCode
	 */
	private $orig_template_code;

	/**
	 * @param $name
	 */
	public function __construct($name)
	{
		$this->name = $name;
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	abstract public function exists();


	/**
	 * @return string
	 */
	abstract public function isCustom();


	/**
	 * @return string
	 */
	abstract public function getContent();


	/**
	 * @return string
	 */
	abstract public function getOriginalContent();


	/**
	 * @return mixed
	 */
	abstract public function getOriginalName();


	/**
	 * @return string
	 */
	abstract public function getType();


	/**
	 * @return EmailTemplateCode|TemplateCode
	 */
	public function getTemplateCode()
	{
		if ($this->template_code !== null) {
			return $this->template_code;
		}

		if ($this->getType() == 'email') {
			$this->template_code = new EmailTemplateCode($this->getContent());
		} else {
			$this->template_code = new TemplateCode($this->getContent());
		}

		return $this->template_code;
	}


	/**
	 * @return EmailTemplateCode|TemplateCode
	 */
	public function getOriginalTemplateCode()
	{
		if (!$this->isCustom()) {
			return null;
		}

		if ($this->orig_template_code !== null) {
			return $this->orig_template_code;
		}

		if ($this->getType() == 'email') {
			$this->orig_template_code = new EmailTemplateCode($this->getOriginalContent());
		} else {
			$this->orig_template_code = new TemplateCode($this->getOriginalContent());
		}

		return $this->orig_template_code;
	}
}