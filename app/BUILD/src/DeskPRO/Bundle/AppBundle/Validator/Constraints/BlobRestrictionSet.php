<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Symfony\Component\Validator\Constraint;

/**
 * Class BlobRestrictionSet.
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class BlobRestrictionSet extends Constraint
{
    const ACCEPT_SIZE                     = 'accept_size';
    const ACCEPT_NOT_ALLOWED_EXTENSION    = 'accept_not_allowed_exts';
    const ACCEPT_NOT_IN_ALLOWED_EXTENSION = 'accept_not_in_allowed_exts';

    public $acceptSizeMessage                  = 'Sorry but this file is too large. Maximum allowed size is {{ detail }}.';
    public $acceptNotAllowedExtensionMessage   = 'You cannot upload a file with any of the following file extensions: {{ detail }}';
    public $acceptNotInAllowedExtensionMessage = 'You can only upload a file with any of the following file extensions: {{ detail }}';

    /**
     * @var CustomDefAbstract
     */
    public $customDef;

    /**
     * @var string
     */
    public $context;
}
