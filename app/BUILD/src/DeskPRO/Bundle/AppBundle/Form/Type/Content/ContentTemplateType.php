<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Content;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\ContentTemplate;
use DeskPRO\Bundle\AppBundle\Form\Type\DateTimeType;
use DeskPRO\Bundle\AppBundle\Form\Type\JsonArrayType;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonAssignType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ContentTemplateType.
 */
class ContentTemplateType extends AbstractType
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
            ->add('title', TextType::class)
            ->add('template', JsonArrayType::class)
            ->add('date_created', DateTimeType::class, [
                'property_path' => 'date_created',
                'widget'        => 'single_text',
                'required'      => false,
            ])
            ->add('type', ChoiceType::class, [
                'multiple'          => false,
                'expanded'          => false,
                'choices_as_values' => true,
                'choices'           => [
                    ContentTemplate::CONTENT_TYPE_ARTICLE,
                    ContentTemplate::CONTENT_TYPE_NEWS,
                    ContentTemplate::CONTENT_TYPE_DOWNLOAD,
                    ContentTemplate::CONTENT_TYPE_TOPIC,

                ],
            ])
//            ->add('attachments', CollectionType::class, [
//                'type'         => 'hidden',
//                'required'     => false,
//                'allow_add'    => true,
//                'allow_delete' => true,
//            ])
            ->add('attach', CollectionType::class, [
                'type'         => 'hidden',
                'required'     => false,
                'allow_add'    => true,
                'allow_delete' => true,
            ])
//            ->add('blob_inline_ids', 'collection', [
//                'type'         => 'hidden',
//                'required'     => false,
//                'allow_add'    => true,
//                'allow_delete' => true,
//            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => ContentTemplate::class,
            ])
            ->setRequired('person')
            ->setAllowedTypes('person', Person::class);
    }
}
