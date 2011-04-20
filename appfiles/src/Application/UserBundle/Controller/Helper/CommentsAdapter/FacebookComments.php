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

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Arrays;
use \Orb\Util\Util;

class FacebookComments extends AbstractComments
{
	/**
	 * Get the HTML block for disqus templates
	 *
	 * @return string
	 */
	public function getHtml()
	{
		$html = App::get('templating')->render('UserBundle:Common:comments-facebook.html.twig', array(
			'entity'           => $this->entity,
			'entity_type'      => get_class($this->entity),
			'entity_basetype'  => Util::getBaseClassname($this->entity),

			'facebook_num_posts' => App::getSetting('core.facebook_comments_num_posts'),
			'facebook_admins'    => App::getSetting('core.facebook_comments_admins'),
			'page_permalink'     => $this->page_url
		));

		return $html;
	}
}