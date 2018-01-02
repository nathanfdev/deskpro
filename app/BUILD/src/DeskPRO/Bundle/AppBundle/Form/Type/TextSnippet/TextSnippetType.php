<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
