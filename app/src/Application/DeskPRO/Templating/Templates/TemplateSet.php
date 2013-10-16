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

use Application\DeskPRO\Translate\Translate;
use Doctrine\ORM\EntityManager;
use Orb\Util\Strings;
use Twig_Environment;

use Application\DeskPRO\Entity\Template as TemplateEntity;

class TemplateSet
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \Twig_Environment
	 */
	private $twig;

	/**
	 * @param EntityManager $em
	 */
	public function __construct(EntityManager $em, Twig_Environment $twig)
	{
		$this->em = $em;
		$this->twig = $twig;
	}


	/**
	 * Create a new email variant with a random ID.
	 *
	 * @param string $name
	 * @return TemplateCustom
	 */
	public function createRandomEmailVariant($name)
	{
		$parts = explode(':', $name);
		array_pop($parts);

		$id = time() . '-' . Strings::random(10, Strings::CHARS_KEY);
		$set_name = implode(':', $parts) . ':custom_' . $id . '.html.twig';

		$entity = new TemplateEntity();
		$entity->variant_of = $name;
		$entity->name = $set_name;

		$template = TemplateCustom::createFromEntity($entity);

		$template->getTemplateCode()->setCode($template->getOriginalTemplateCode()->getCode());

		$entity->setTemplate(
			$template->getTemplateCode()->getCode(),
			$this->compileTemplate($template)
		);

		return $template;
	}


	/**
	 * @param string $name
	 * @return TemplateCustom|TemplateFile
	 */
	public function getTemplate($name)
	{
		$custom = $this->em->getRepository('DeskPRO:Template')->getTemplateByName($name);
		if ($custom) {
			$template = TemplateCustom::createFromEntity($custom);
		} else {
			$template = new TemplateFile($name);
		}

		return $template;
	}


	/**
	 * Persists code saved in the template_code
	 */
	public function saveTemplate(TemplateCustom $template)
	{
		$entity = $template->getEntity();
		$entity->name = $template->getName();
		$entity->setTemplate(
			$template->getTemplateCode()->getCode(),
			$this->compileTemplate($template)
		);
		$entity->date_updated = new \DateTime();

		if ($template->getOriginalName() && $template->getOriginalName() != $template->getName()) {
			$entity->variant_of = $template->getOriginalName();
		}

		$this->em->persist($entity);
		$this->em->flush();
	}


	/**
	 * @param Template $template
	 * @return string
	 */
	public function compileTemplate(Template $template)
	{
		$name = $template->getName();
		if ($template->isCustom() && $template->getOriginalName() != $name) {
			$name = $template->getOriginalName();
		}

		$template_code = $template->getTemplateCode();
		if ($template_code instanceof EmailTemplateCode) {
			$proc = new \Application\DeskPRO\Twig\PreProcessor\EmailPreProcessor();
			$compile_code = $proc->process($template_code->getCode(), $template->getName());
		} else {
			$compile_code = $template_code->getCode();
		}

		if ($template->isCustom()) {
			$compile_code = $this->preProcessCustomTemplate($compile_code);
		}

		$compiled = $this->twig->compileSource($compile_code, $name);
		return $compiled;
	}


	/**
	 * @param $code
	 * @return mixed
	 */
	private function preProcessCustomTemplate($code)
	{
		$code = preg_replace('#\{%\s*include\s+(\'|")(.*?)(\'|")\s+#', '{% include \'$2\' ignore missing ', $code);
		return $code;
	}


	/**
	 * @param Template $template
	 * @return array
	 */
	public function exportTemplateToArray(Template $template, Translate $tr = null, $replace_phrases = false)
	{
		$data = array();
		$data['name'] = $template->getName();
		$data['base_name'] = $data['name'];
		$data['type'] = $template->getType();
		$data['is_custom'] = $template->isCustom();
		$data['template_code'] = array();
		$data['template_code']['code'] = $template->getTemplateCode()->getCode();

		if ($template->getType() == 'email') {
			$data['template_code']['subject'] = $template->getTemplateCode()->getSubject();
			$data['template_code']['body'] = $template->getTemplateCode()->getBody();
		}

		if ($template->getOriginalName()) {
			$data['base_name'] = $template->getOriginalName();
			$data['original'] = array(
				'name' => $template->getOriginalName(),
				'template_code' => array()
			);

			$data['original']['template_code']['code'] = $template->getTemplateCode()->getCode();
			if ($template->getType() == 'email') {
				$data['original']['template_code']['subject'] = $template->getTemplateCode()->getSubject();
				$data['original']['template_code']['body'] = $template->getTemplateCode()->getBody();
			}
		}

		if ($tr) {
			$key = 'admin.emailtpl_desc.' . strtolower(str_replace(array(':', '.'), '_', $data['base_name']));
			$data['display_title'] = $tr->phrase($key.'_title');
			$data['display_description'] = $tr->phrase($key.'_desc');
		}

		if ($tr && $replace_phrases) {
			foreach ($data['template_code'] as $k => $v) {
				$data['template_code'][$k] = $this->resolvePhraseTags($v, $tr);
			}
		}

		return $data;
	}


	/**
	 * Replace phrase tags with actual language
	 *
	 * @param string $code
	 * @param Translate $tr
	 * @return string
	 */
	public function resolvePhraseTags($code, Translate $tr)
	{
		$code = preg_replace_callback('#\{\{\s*phrase\((\"|\')([a-zA-Z0-9_\-\.]+)\\1\)\s*\}\}#', function($m) use ($tr) {
			$phrase = $tr->phrase($m[2]);
			if ($phrase) {
				return $phrase;
			} else {
				return $m[0];
			}
		}, $code);

		return $code;
	}
}