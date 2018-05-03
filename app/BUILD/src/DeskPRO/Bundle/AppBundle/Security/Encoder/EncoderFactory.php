<?php

namespace DeskPRO\Bundle\AppBundle\Security\Encoder;

use Application\DeskPRO\Entity\Person;

/**
 * Class EncoderFactory.
 */
class EncoderFactory extends \Symfony\Component\Security\Core\Encoder\EncoderFactory
{
    /**
     * {@inheritdoc}
     */
    public function getEncoder($user)
    {
        if ($user instanceof Person) {
            return new PersonPasswordEncoder($user);
        }

        return parent::getEncoder($user);
    }
}
