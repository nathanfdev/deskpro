<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\Attachments\AcceptAttachment;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Orb\Util\Env as EnvUtil;

class AttachmentAccepterService
{
    public static function create(DeskproContainer $container)
    {
        $accepter = new AcceptAttachment(
            $container->getEm(),
            $container->getBlobStorage()
        );

        $effective_max_size = EnvUtil::getEffectiveMaxUploadSize();

        foreach (['', 'emails.'] as $prefix) {
            foreach (['agent', 'user'] as $type) {
                $res = new \Application\DeskPRO\Attachments\RestrictionSet();

                $max_size = $container->getSetting('core.'.$prefix.'attach_'.$type.'_maxsize');

                if ($prefix != 'emails.') {
                    $max_size = min($effective_max_size, $max_size);
                }

                $must_exts = $container->getSetting('core.'.$prefix.'attach_'.$type.'_must_exts');
                $not_exts  = $container->getSetting('core.'.$prefix.'attach_'.$type.'_not_exts');

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

                $accepter->addRestrictionSet($prefix.$type, $res);
            }
        }

        return $accepter;
    }
}
