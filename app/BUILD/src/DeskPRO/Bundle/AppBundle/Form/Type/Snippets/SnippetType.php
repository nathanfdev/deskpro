<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Snippets;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\Snippet;
use DeskPRO\Bundle\AppBundle\Entity\SnippetLabel;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Form\Type\Labels\LabelsCollectionType;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonAssignType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SnippetType.
 */
class SnippetType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('person', PersonAssignType::class, [
                'person'   => $options['person'],
                'required' => false,
            ])
            ->add('title', TextType::class, [
                'required' => true,
            ])
            ->add('labels', LabelsCollectionType::class, [
                'labels_class'   => SnippetLabel::class,
                'labels_owner'   => $builder->getData(),
                'owner_property' => 'snippet',
                'required'       => false,
            ])
            ->add('types', ChoiceType::class, [
                'required'          => true,
                'multiple'          => true,
                'choices_as_values' => true,
                'choices'           => [Snippet::TYPE_CHAT, Snippet::TYPE_TICKET],
            ])
            ->add('translations', CollectionType::class, [
                'entry_type'     => SnippetTranslationType::class,
                'allow_add'      => true,
                'allow_delete'   => true,
                'error_bubbling' => false,
                'options'        => [
                    'error_bubbling' => true,
                    'snippet'        => $builder->getData(),
                ],
                'by_reference' => false,
                'required'     => false,
            ])
            ->add('shortcut_code', TextType::class)
            ->add('is_draft', ApiBooleanType::class, [
                'required' => false,
            ])
            ->add('is_split', ApiBooleanType::class, [
                'required' => false,
            ])
            ->add('is_ownership_global', ApiBooleanType::class, [
                'required' => false,
            ])
            ->add('is_visible_global', ApiBooleanType::class, [
                'required' => false,
            ])
            ->add('ownership_teams', EntityType::class, [
                'class'    => AgentTeam::class,
                'multiple' => true,
            ])
            ->add('visible_departments', EntityType::class, [
                'class'    => Department::class,
                'multiple' => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('person')
            ->setDefaults([
                'data_class' => Snippet::class,
            ])
            ->setAllowedTypes('person', Person::class)
        ;
    }
}
