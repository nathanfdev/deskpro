<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category DependencyInjection
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Attachments\AcceptAttachment;

use Application\DeskPRO\App;

use Orb\Util\Env as EnvUtil;

class AttachmentAccepterService
{
	public static function create(DeskproContainer $container)
	{
		$accepter = new AcceptAttachment(
			$container->getEm(),
			App::getApi('filestorage')
		);

		$effective_max_size = EnvUtil::getEffectiveMaxUploadSize();

		foreach (array('agent', 'user') as $type) {
			$res = new \Application\DeskPRO\Attachments\RestrictionSet();

			$max_size  = $container->getSetting('core.attach_'.$type.'_maxsize');
			$max_size  = min($effective_max_size, $max_size);

			$must_exts = $container->getSetting('core.attach_'.$type.'_must_exts');
			$not_exts  = $container->getSetting('core.attach_'.$type.'_not_exts');

			if ($must_exts) {
				$must_exts = explode(',', strtolower($must_exts));
				array_walk($must_exts, 'trim');
			} else {
				$must_exts = null;
			}

			if ($not_exts) {
				$not_exts = explode(',', strtolower($not_exts));
				array_walk($not_exts, 'trim');
			} else {
				$not_exts = null;
			}

			$res->setMaxSize($max_size)->setAllowedExts($must_exts)->setDisallowedExts($not_exts);

			$accepter->addRestrictionSet($type, $res);
		}

		return $accepter;
	}
}
