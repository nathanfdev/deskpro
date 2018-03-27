<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Application\DeskPRO\Attachments\AcceptAttachment;
use Application\DeskPRO\Attachments\RestrictionSet;
use Application\DeskPRO\Entity\Blob;
use Orb\Util\Numbers;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class BlobRestrictionSetValidator.
 */
class BlobRestrictionSetValidator extends ConstraintValidator
{
    /**
     * @var AcceptAttachment
     */
    private $acceptAttachment;

    /**
     * Constructor.
     *
     * @param AcceptAttachment $acceptAttachment
     */
    public function __construct(AcceptAttachment $acceptAttachment)
    {
        $this->acceptAttachment = $acceptAttachment;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof BlobRestrictionSet) {
            throw new UnexpectedTypeException($constraint, BlobRestrictionSet::class);
        }

        if (!$value) {
            return;
        }
        if (!$value instanceof Blob) {
            throw new UnexpectedTypeException($value, Blob::class);
        }

        $setId = $constraint->context;
        if ($constraint->customDef) {
            $setId = RestrictionSet::getSetIdForCustomField($constraint->customDef, $constraint->context);
        }

        $set   = $this->acceptAttachment->getRestrictionSet($setId);
        $props = [
            'size' => $value->getFilesize(),
            'ext'  => $value->getExtension(),
        ];

        if ($error = $set->getErrorForProperties($props)) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;

            if ($error['error_code'] === RestrictionSet::ERR_SIZE) {
                $context
                    ->buildViolation($constraint->acceptSizeMessage)
                    ->setParameter('detail', Numbers::filesizeDisplay($set->getMaxSize()))
                    ->setCode(BlobRestrictionSet::ACCEPT_SIZE)
                    ->addViolation()
                ;
            } elseif ($error['error_code'] === RestrictionSet::ERR_FAIL_MUST_EXT) {
                $context
                    ->buildViolation($constraint->acceptNotInAllowedExtensionMessage)
                    ->setParameter('detail', implode(',', $set->getAllowedExts()))
                    ->setCode(BlobRestrictionSet::ACCEPT_NOT_IN_ALLOWED_EXTENSION)
                    ->addViolation()
                ;
            } elseif ($error['error_code'] === RestrictionSet::ERR_FAIL_NOT_EXT) {
                $context
                    ->buildViolation($constraint->acceptNotAllowedExtensionMessage)
                    ->setParameter('detail', implode(',', $set->getDisallowedExts()))
                    ->setCode(BlobRestrictionSet::ACCEPT_NOT_ALLOWED_EXTENSION)
                    ->addViolation()
                ;
            }
        }
    }
}
