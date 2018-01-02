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

namespace DeskPRO\Bundle\AppBundle\Form\Type\UserChat;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Form\Type\HtmlTextareaType;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonAssignType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ChatMessageType.
 */
class ChatMessageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', HtmlTextareaType::class, [
                'property_path' => 'content',
                'required'      => true,
            ])
            ->add('author', PersonAssignType::class, [
                'property_path' => 'author',
                'person'        => $options['person'],
                'required'      => false,
            ])
            ->add('is_user', ApiBooleanType::class, [
                'required' => false,
            ])
            ->add('person_name', TextType::class, [
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['person', 'conversation'])
            ->setDefaults([
                'data_class' => ChatMessage::class,
            ])
            ->setAllowedTypes('person', Person::class)
            ->setAllowedTypes('conversation', ChatConversation::class)
        ;
    }

    /**
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form   = $event->getForm();
        $config = $form->getConfig();

        /** @var ChatMessage $message */
        $message = $form->getData();
        if ($message->getAuthor()) {
            $origin = $message->getAuthor()->isAgent() ? 'agent' : 'user';
        } else {
            $origin = $message->getIsUser() ? 'user' : 'agent';
        }

        $message->setOrigin($origin);

        /** @var ChatConversation $conversation */
        $conversation = $config->getOption('conversation');
        $conversation->addMessage($message);
    }
}
