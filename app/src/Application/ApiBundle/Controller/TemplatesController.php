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

namespace Application\ApiBundle\Controller;

use Application\DeskPRO\Templating\Templates\EmailTemplateCode;
use Application\DeskPRO\Templating\Templates\TemplateCode;
use Application\DeskPRO\Templating\Templates\TemplateSet;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class TemplatesController extends AbstractController
{
	####################################################################################################################
	# get-template
	####################################################################################################################

	public function getTemplateAction($name)
	{
		$set = $this->getTemplateSet();

		try {
			$template = $set->getTemplate($name);
		} catch (\InvalidArgumentException $e) {
			throw $this->createNotFoundException();
		}

		$data = $set->exportTemplateToArray(
			$template,
			$this->container->getTranslator(),
			!$this->container->getLanguageData()->isMultiLang()
		);

		return $this->createApiResponse($data);
	}

	####################################################################################################################
	# create-variant
	####################################################################################################################

	public function createRandomVariantAction($name)
	{
		$set = $this->getTemplateSet();

		$template = $set->createRandomEmailVariant($name);
		$entity = $template->getEntity();

		$this->em->persist($entity);
		$this->em->flush();

		$data = $set->exportTemplateToArray(
			$template,
			$this->container->getTranslator(),
			!$this->container->getLanguageData()->isMultiLang()
		);

		return $this->createApiResponse($data);
	}

	####################################################################################################################
	# set-template
	####################################################################################################################

	public function setTemplateAction($name)
	{
		$set = $this->getTemplateSet();

		try {
			$template = $set->getTemplate($name);
		} catch (\InvalidArgumentException $e) {
			throw $this->createNotFoundException();
		}

		$template_code = $template->getTemplateCode();

		if ($template->getType() == 'email') {
			$subject = $this->in->getString('template.subject');
			$body    = $this->in->getString('template.body');

			$template_code->setSubject($subject);
			$template_code->setBody($body);
		} else {
			$code = $this->in->getString('template.code');
			$template_code->setCode($code);
		}

		$set->saveTemplate($template);
	}


	/**
	 * @return TemplateSet
	 */
	private function getTemplateSet()
	{
		$set = new TemplateSet(
			$this->em,
			$this->container->get('twig')
		);
		return $set;
	}
}