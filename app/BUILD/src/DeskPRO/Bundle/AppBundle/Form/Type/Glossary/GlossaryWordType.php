<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Glossary;

use Application\DeskPRO\Entity\GlossaryWord;
use Application\DeskPRO\Entity\GlossaryWordDefinition;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class GlossaryWordType.
 */
class GlossaryWordType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('word', TextType::class, [
                'required' => true,
            ])
            ->add('definition', EntityType::class, [
                'class'    => GlossaryWordDefinition::class,
                'required' => true,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GlossaryWord::class,
        ]);
    }
}
