<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Util
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO;

use Symfony\Component\Routing\Generator\UrlGenerator;

use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Gets the URL to the page for a resource given a context
 */
class ObjectUrlGenerator
{
	const CONTEXT_AGENT = 'agent';
	const CONTEXT_USER  = 'user';

	protected $generator;

	public function __construct(UrlGenerator $generator)
	{
		$this->generator = $generator;
	}

	public function generateObjectUrl($object, array $params = array(), $context = null)
	{
		$typename = get_class($object);

		switch ($typename) {
			case 'Application\\DeskPRO\\Entity\\Article':
				if ($context == 'agent') {
					$params['article_id'] = $object['id'];
					return $this->generator->generate('agent_kb_article', $params);
				}
				return $object->getUrlSlug();
				break;

			case 'Application\\DeskPRO\\Entity\\Download':
				if ($context == 'agent') {
					$params['download_id'] = $object['id'];
					return $this->generator->generate('agent_downloads_view', $params);
				}
				return $object->getUrlSlug();
				break;

			case 'Application\\DeskPRO\\Entity\\Idea':
				if ($context == 'agent') {
					$params['idea_id'] = $object['id'];
					return $this->generator->generate('agent_ideas_view', $params);
				}
				return $object->getUrlSlug();
				break;

			case 'Application\\DeskPRO\\Entity\\News':
				if ($context == 'agent') {
					$params['news_id'] = $object['id'];
					return $this->generator->generate('agent_news_view', $params);
				}
				return $object->getUrlSlug();
				break;

			case 'Application\\DeskPRO\\Entity\\Person':
				if ($context == 'agent') {
					$params['person_id'] = $object['id'];
					return $this->generator->generate('agent_people_view', $params);
				}
				break;
		}

		return null;
	}
}