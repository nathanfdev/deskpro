<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Glossary;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class GlossaryWordDefinitionType.
 */
class GlossaryWordDefinitionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('definition', TextType::class, [
                'required' => true,
            ])
            ->add('words', CollectionType::class, [
                'entry_type'   => GlossaryWordType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required'     => true,
            ]);
    }
}
