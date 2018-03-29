<?php

namespace DeskPRO\Bundle\ImportBundle\Form\Type\Source;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class KayakoSourceType.
 */
class KayakoSourceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('dbinfo', DbInfoType::class);
    }
}
