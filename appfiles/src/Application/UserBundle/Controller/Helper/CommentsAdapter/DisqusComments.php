<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\UserBundle\Controller\Helper\CommentsAdapter;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Arrays;
use Orb\Util\Util;

class DisqusComments extends AbstractComments
{
	/**
	 * Get the HTML block for disqus templates
	 *
	 * @return string
	 */
	public function getHtml()
	{
		$html = App::get('templating')->render('UserBundle:Common:comments-disqus.html.twig', array(
			'entity'           => $this->entity,
			'entity_type'      => get_class($this->entity),
			'entity_basetype'  => Util::getBaseClassname($this->entity),

			'disqus_shortname' => App::getSetting('core.disqus_shortname'),
			'page_id'          => $this->page_id,
			'page_permalink'   => $this->page_url
		));

		return $html;
	}
}
