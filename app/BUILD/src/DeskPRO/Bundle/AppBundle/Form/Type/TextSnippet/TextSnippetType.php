<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\TextSnippet;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TextSnippet;
use Application\DeskPRO\Entity\TextSnippetCategory;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Form\Type\ObjectLang\ObjectLangCollectionType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TextSnippetType.
 */
class TextSnippetType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', ObjectLangCollectionType::class, [
                'mapped'    => false,
                'prop_name' => 'title',
                'owner'     => $builder->getData(),
                'required'  => true,
            ])
            ->add('snippet', ObjectLangCollectionType::class, [
                'mapped'    => false,
                'prop_name' => 'snippet',
                'owner'     => $builder->getData(),
                'required'  => true,
            ])
            ->add('category', EntityType::class, [
                'required'      => true,
                'class'         => TextSnippetCategory::class,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    $qb = $er
                        ->createQueryBuilder('d')
                        ->where(
                            'd.typename = :typename',
                            'd.person = :person or d.is_global = true'
                        )
                        ->setParameters([
                            'typename' => $options['type'],
                            'person'   => $options['person'],
                        ])
                    ;

                    return $qb;
                },
            ])
            ->add('shortcut_code', TextType::class, [
                'required' => true,
            ])
            ->add('is_draft', ApiBooleanType::class, [
                'required' => false,
            ])
            ->add('is_global', ApiBooleanType::class, [
                'mapped'   => false,
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onSetPerson']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'    => TextSnippet::class,
                'error_mapping' => [
                    'titleTranslations'   => 'title',
                    'snippetTranslations' => 'snippet',
                ],
            ])
            ->setRequired(['type', 'person'])
            ->setAllowedTypes('person', Person::class)
            ->setAllowedValues('type', [
                TextSnippetCategory::TYPE_TICKET,
                TextSnippetCategory::TYPE_CHAT,
            ])
        ;
    }

    /**
     * @param FormEvent $event
     */
    public function onSetPerson(FormEvent $event)
    {
        $form   = $event->getForm();
        $config = $event->getForm()->getConfig();

        /* @var TextSnippet $data */
        $snippet = $form->getData();

        $data = $event->getData();
        if (isset($data['is_global']) && $data['is_global']) {
            $snippet->setPerson(null);
        } else {
            $person = $config->getOption('person');
            $snippet->setPerson($person);
        }
    }
}
