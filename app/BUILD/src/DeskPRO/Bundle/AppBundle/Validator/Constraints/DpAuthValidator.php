<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
