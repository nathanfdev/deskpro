<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity\Avatar;

use Application\DeskPRO\Entity\Blob;

/**
 * Interface AvatarOwner.
 */
interface AvatarOwner
{
    /**
     * @return Blob
     */
    public function getAvatarBlob();
}
