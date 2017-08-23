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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Snippets;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Language;
use DeskPRO\Bundle\AppBundle\Entity\Snippet;
use DeskPRO\Bundle\AppBundle\Entity\SnippetTranslation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SnippetTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('language', EntityType::class, [
                'class' => Language::class,
            ])
            ->add('content', TextType::class)
            ->add('blobs', EntityType::class, [
                'class'    => Blob::class,
                'multiple' => true,
            ])
            ->add('snippet', EntityType::class, [
                'class' => Snippet::class,
            ])
            ->add('type', ChoiceType::class, [
                'choices_as_values' => true,
                'choices'           => [Snippet::TYPE_CHAT, Snippet::TYPE_TICKET],
            ])
            ->add('id', TextType::class, [
                'mapped'   => false,
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('snippet')
            ->setDefaults([
                'data_class' => SnippetTranslation::class,
            ])
            ->setAllowedTypes('snippet', Snippet::class)
        ;
    }

    /**
     * @param FormEvent $event
     */
    public function onSetRelations(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $form->getData();

        if ($data instanceof SnippetTranslation) {
            $data->setSnippet($form->getConfig()->getOption('snippet'));
        }
    }
}
