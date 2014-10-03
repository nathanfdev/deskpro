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
 * @category Install
 */

namespace Application\InstallBundle\Data\DefaultData;


use Application\DeskPRO\Templating\Templates\TemplateSet;

class TemplateData extends AbstractDefaultData
{
	public function runInstall()
	{
		$this->getLogger()->debug("Saving default template");
		$this->getDb()->executeUpdate("DELETE FROM templates WHERE name = 'UserBundle:Portal:welcome-block.html.twig'");
		$style = $this->getEm()->getRepository('DeskPRO:Style')->findAll();
		$style = $style[0];

		$set = new TemplateSet(
			$this->getEm(),
			$this->getContainer()->get('twig'),
			$style
		);
		$template = $set->getCustomTemplate('UserBundle:Portal:welcome-block.html.twig');
		$template_code = $template->getTemplateCode();
		$template_code->setCode($this->getCode());
		$set->saveTemplate($template);
	}

	public function runReset()
	{
		$this->runInstall();
	}

	public function runSync()
	{
		return;
	}

	private function getCode()
	{
		return <<<'HTML'
{##
 # This is the block displayed at the top of the portal home page.
 ##}

<article class="dp-intro-box">
	<h2>Welcome</h2>
	<p>
		This is your new installation of DeskPRO. Why don't you try
		<a href="{{ path('user_tickets_new') }}">submitting a new ticket</a> to test out your new helpdesk?
	</p>
	<p>
		Here are some ideas on what to do next:
	</p>
	<ul>
		<li>Change this welcome text from <a href="{{ path('user') }}admin/#/portal/portal_editor">Admin Interface {{ language_arrow('right') }} Portal</a></li>
		<li>Integrate with your website using the Javascript widgets from <a href="{{ path('user') }}admin/#/portal/embeds">Admin Interface {{ language_arrow('right') }} Portal {{ language_arrow('right') }} Embed</a></li>
		<li>Add some new knowledgebase articles from <a href="{{ path('user') }}agent">Agent Interface {{ language_arrow('right') }} Publish</a></li>
	</ul>
	<p>
		If you are new to DeskPRO, we recommend reading through our <a href="https://support.deskpro.com/kb/articles/127-getting-started">Getting Started Guide</a>.
		Remember, if you need help you can always contact us through <a href="http://support.deskpro.com/">support.deskpro.com</a>!
	</p>
</article>
HTML;
	}
}