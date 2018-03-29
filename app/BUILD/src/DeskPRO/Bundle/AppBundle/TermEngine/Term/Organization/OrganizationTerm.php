<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\Organization;

use DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver\TermOptionsResolver;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\PrimaryKeyExists;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class OrganizationTerm.
 */
class OrganizationTerm extends AbstractTerm
{
    /**
     * {@inheritdoc}
     */
    public static function configureOptions(TermOptionsResolver $resolver)
    {
        $resolver->setDefaults(['organization' => null]);
        $resolver->setConstraints([
            new Assert\NotBlank(),
            new PrimaryKeyExists(['table' => 'organizations']),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedOps()
    {
        return [TermInterface::OP_IS, TermInterface::OP_NOT];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOp()
    {
        return TermInterface::OP_IS;
    }
}
