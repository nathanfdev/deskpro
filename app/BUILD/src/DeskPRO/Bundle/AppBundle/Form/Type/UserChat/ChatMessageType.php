<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\UserChat;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Form\Type\BlobAuthType;
use DeskPRO\Bundle\AppBundle\Form\Type\HtmlTextareaType;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonAssignType;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use JMS\Serializer\Serializer;
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
     * @var Serializer
     */
    private $serializer;

    /**
     * Constructor.
     *
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

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
            ->add('blob', BlobAuthType::class, [
                'required' => false,
                'mapped'   => false,
            ])
        ;

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit']);
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
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $blob = $form->get('blob')->getData();
        $data = $event->getData();

        if (!$data instanceof ChatMessage) {
            return;
        }

        if ($blob instanceof Blob) {
            // Support old attachment message format
            $content = sprintf(
                'File: <a href="%s" target="_blank">%s</a> (%s)',

                $blob->getDownloadUrl(true),
                htmlspecialchars($blob->getFilename()),
                $blob->getReadableFilesize()
            );

            if ($blob->isImage()) {
                $content .= sprintf('<div class="file-thumb"><img style="max-width: 50px; max-height: 50px" src="%s" /></div>', $blob->getThumbnailUrl(50, true));
            }

            $data->setContent($content);
            $data->setMetadata(array_merge($data->getMetadata(), [
                'type'    => 'file',
                'blob_id' => $blob->getId(),
                'blob'    => $this->serializer->toArray($blob, new SideloadSerializationContext()),
            ]));
        }
    }

    /**
     * @internal
     *
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
        $message->setIsHtml(true);

        /** @var ChatConversation $conversation */
        $conversation = $config->getOption('conversation');
        $conversation->addMessage($message);
    }
}
