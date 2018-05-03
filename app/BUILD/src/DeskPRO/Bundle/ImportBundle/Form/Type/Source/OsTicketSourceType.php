<?php

namespace DeskPRO\Bundle\ImportBundle\Form\Type\Source;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class OsTicketSourceType.
 */
class OsTicketSourceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dbinfo', DbInfoType::class)
            ->add('table_prefix', TextType::class)
        ;
    }
}
