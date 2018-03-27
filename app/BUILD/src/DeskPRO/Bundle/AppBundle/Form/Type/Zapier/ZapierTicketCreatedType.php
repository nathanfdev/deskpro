<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Zapier;

use Application\DeskPRO\Entity\LegacyTicketFilter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ZapierTicketCreatedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('filter', EntityType::class, [
            'class' => LegacyTicketFilter::class,
        ]);
    }
}
