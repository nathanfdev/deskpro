<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\AgentChat;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Form\Type\HtmlTextareaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AgentChatMessageType.
 */
class AgentChatMessageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message', HtmlTextareaType::class, [
                'html_type' => 'extended_html',
                'required'  => true,
            ])
            ->add('uuid', TextType::class, [
                'required' => true,
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSetRelations'], 100);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['person', 'chat'])
            ->setDefaults([
                'data_class' => AgentChatMessage::class,
            ])
            ->setAllowedTypes('person', Person::class)
            ->setAllowedTypes('chat', AgentChat::class)
        ;
    }

    /**
     * @param FormEvent $event
     */
    public function onSetRelations(FormEvent $event)
    {
        $form   = $event->getForm();
        $config = $form->getConfig();

        /** @var AgentChatMessage $message */
        $message = $form->getData();
        /** @var AgentChat $chat */
        $chat = $config->getOption('chat');
        /** @var Person $person */
        $person = $config->getOption('person');

        $message->setPerson($person);
        $chat->addMessage($message);
    }
}
