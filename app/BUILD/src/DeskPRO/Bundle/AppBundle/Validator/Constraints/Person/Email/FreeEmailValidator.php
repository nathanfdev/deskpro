<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Person\Email;

use Application\DeskPRO\Entity\PersonEmail;
use Doctrine\ORM\EntityManager;

/**
 * Class FreeEmailValidator.
 */
class FreeEmailValidator extends AbstractEmailValidator
{
    /**
     * @var EntityManager
     */
    protected $em;

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
    protected function isValidEmail(PersonEmail $value)
    {
        $existEmail = $this->em->getRepository(PersonEmail::class)->findOneBy([
            'email' => $value->getEmail(),
        ]);

        return !$existEmail || $existEmail->getId() === $value->getId();
    }
}
