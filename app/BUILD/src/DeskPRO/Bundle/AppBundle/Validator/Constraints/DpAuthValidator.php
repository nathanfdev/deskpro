<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Application\DeskPRO\Auth\AuthenticationManager;
use DeskPRO\Bundle\AppBundle\Form\Error\ErrorsCodes;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class DpAuthValidator.
 */
class DpAuthValidator extends ConstraintValidator
{
    /**
     * @var AuthenticationManager
     */
    private $authentication_manager;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param AuthenticationManager $authentication_manager
     * @param EntityManager         $em
     */
    public function __construct(AuthenticationManager $authentication_manager, EntityManager $em)
    {
        $this->authentication_manager = $authentication_manager;
        $this->em                     = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $email    = $value['email'];
        $password = $value['password'];

        // todo temporary controller
        // todo just for agents for now
        $auth_result = $this->authentication_manager->authenticateFormLogin($email, $password);

        if (!$auth_result->isValid()) {
            /** @var \Application\DeskPRO\EntityRepository\Person $person_repository */
            $person_repository = $this->em->getRepository('DeskPRO:Person');
            $person            = $person_repository->findOneByEmail($email);

            if (!$person) {
                $this->context->addViolationAt('email', 'No such account was found', [], $email, null, ErrorsCodes::BAD_CREDENTIALS);
            } else {
                $this->context->addViolationAt('password', 'Looks like this isn\'t the correct password', [], $password, null, ErrorsCodes::BAD_CREDENTIALS);
            }
        }
    }
}
