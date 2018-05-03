<?php

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
