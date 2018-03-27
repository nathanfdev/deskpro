<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\CustomField;

use Application\DeskPRO\Entity\Blob;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class FileValidator.
 */
class FileValidator extends AbstractCustomDefConstraintValidator
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidators($data, AbstractCustomDefConstraint $constraint)
    {
        $validators = [];

        // required validator
        if ($constraint->getCustomDefOption('required', true)) {
            $validators[] = new Assert\NotBlank();
        }

        // multiple validator
        if (!$constraint->custom_def->getOption('multiple')) {
            $validators[] = new Assert\Count(['max' => 1]);
        }

        $validators[] = new Assert\All([
            'constraints' => [
                new Assert\NotNull(),
                new AppAssert\BlobRestrictionSet([
                    'customDef' => $constraint->custom_def,
                    'context'   => $constraint->context,
                ]),
            ],
        ]);

        return $validators;
    }

    /**
     * {@inheritdoc}
     */
    protected function getData(Collection $value, AbstractCustomDefConstraint $constraint)
    {
        $blobs = [];
        foreach ($value as $customData) {
            $blobId  = $customData->getValue();
            $blobs[] = $blobId ? $this->em->getRepository(Blob::class)->find($blobId) : null;
        }

        return $blobs;
    }
}
