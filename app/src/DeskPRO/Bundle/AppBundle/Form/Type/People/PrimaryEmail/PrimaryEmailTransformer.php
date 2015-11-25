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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\Form\Type\People\PrimaryEmail;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class PrimaryEmailTransformer.
 */
class PrimaryEmailTransformer implements DataTransformerInterface
{
    /**
     * @var Person
     */
    private $person;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param Person        $person
     * @param EntityManager $em
     */
    public function __construct(Person $person, EntityManager $em)
    {
        $this->person = $person;
        $this->em     = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (is_null($value)) {
            return $value;
        }

        return $value->getEmail();
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (!$email = $this->em->getRepository(PersonEmail::class)->findOneBy(['email' => $value])) {
            $email = new PersonEmail();
            $email->setEmail($value);
            $email->setPerson($this->person);
            $this->person->addEmail($email);
        }

        return $email;
    }
}
