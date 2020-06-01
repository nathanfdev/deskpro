<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Person\Email;

use Application\DeskPRO\Entity\PersonEmail;
use DeskPRO\Bundle\AppBundle\Entity\SavedForm;
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

        if ($existEmail && $existEmail->getId() === $value->getId()) {
            return true;
        }

        // check pending emails as well
        $qb = $this->em->getRepository(SavedForm::class)->createQueryBuilder('f');
        $qb
            ->select('f')
            ->where('f.intention_type = :type')
            ->andWhere('f.form_data LIKE :email')
            ->setParameter('type', SavedForm::INTENTION_VERIFY_EMAIL)
            ->setParameter('email', '%'.$value->getEmail().'%')
        ;

        $savedForms = $qb->getQuery()->getResult();

        return !$existEmail && !$savedForms;
    }
}
