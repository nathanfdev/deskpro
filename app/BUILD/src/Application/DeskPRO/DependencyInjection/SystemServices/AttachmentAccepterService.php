<?php

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\Attachments\AcceptAttachment;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Orb\Util\Env as EnvUtil;

/**
 * Class AttachmentAccepterService.
 */
class AttachmentAccepterService
{
    /**
     * @param DeskproContainer $container
     *
     * @return AcceptAttachment
     */
    public static function create(DeskproContainer $container)
    {
        $accepter = new AcceptAttachment(
            $container->getEm(),
            $container->getBlobStorage()
        );

        $effectiveMaxUploadSize = EnvUtil::getEffectiveMaxUploadSize();

        foreach (['', 'emails.'] as $prefix) {
            foreach (['agent', 'user'] as $type) {
                $res = new \Application\DeskPRO\Attachments\RestrictionSet();

                $maxSize = $container->getSetting('core.'.$prefix.'attach_'.$type.'_maxsize');
                if ($prefix != 'emails.') {
                    $maxSize = min($effectiveMaxUploadSize, $maxSize);
                }

                $mustExtensions = $container->getSetting('core.'.$prefix.'attach_'.$type.'_must_exts');
                $notExtensions  = $container->getSetting('core.'.$prefix.'attach_'.$type.'_not_exts');

                if ($mustExtensions) {
                    $mustExtensions = explode(',', strtolower($mustExtensions));
                    array_walk($mustExtensions, 'trim');
                } else {
                    $mustExtensions = null;
                }

                if ($notExtensions) {
                    $notExtensions = explode(',', strtolower($notExtensions));
                    array_walk($notExtensions, 'trim');
                } else {
                    $notExtensions = null;
                }

                $res->setMaxSize($maxSize)->setAllowedExts($mustExtensions)->setDisallowedExts($notExtensions);

                $accepter->addRestrictionSet($prefix.$type, $res);
            }
        }

        return $accepter;
    }
}
