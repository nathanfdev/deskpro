<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

use Application\DeskPRO\EntityRepository\Person;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class FreeEmailValidator.
 */
class FreeEmailValidator extends ConstraintValidator
{
    /**
     * @var Person
     */
    private $person_repository;

    /**
     * Constructor.
     *
     * @param Person $person_repository
     */
    public function __construct(Person $person_repository)
    {
        $this->person_repository = $person_repository;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof FreeEmail) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\FreeEmail');
        }

//        $existPersons = $this->person_repository->findByEmails($set_emails);
//        $agent        = reset($existPersons);
//
//        // we have a dupe email error
//        // not yet
//        $dupe =
//            ($id && $agent && $agent['id'] != $id) // update an agent (or user to agent)
//            ||
//            (!$id && $agent && $agent['is_agent']); // insert an agent
//
//        if ($dupe) {
//            $error_info = array('existing' => array());
//
//            foreach ($existPersons as $person) {
//                $error_info['existing'][] = array(
//                    'person_id'   => $person['id'],
//                    'person_name' => $person['display_name'],
//                    'email'       => implode(', ', $person->getEmailAddresses()),
//                );
//            }
//
//            return $this->createApiErrorInfoResponse('dupe_email', 'One or more email addresses are already in use by other users', $error_info);
//        }
    }
}
