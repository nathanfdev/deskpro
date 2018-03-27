<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Person\Email;

use Application\DeskPRO\Entity\BanEmail;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\EntityRepository;
use Doctrine\ORM\EntityManager;

/**
 * Class NotBannedEmailValidator.
 */
class NotBannedEmailValidator extends AbstractEmailValidator
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
        /** @var EntityRepository\BanEmail $repository */
        $repository = $this->em->getRepository(BanEmail::class);

        return !$repository->isEmailBanned($value->getEmail());
    }
}
