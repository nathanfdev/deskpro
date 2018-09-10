<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMacro;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Tickets\TicketActions\AbstractReplyAction;
use Application\DeskPRO\Tickets\TicketActions\ActionsCollection;
use Application\DeskPRO\Tickets\TicketActions\StatusAction;
use DeskPRO\Bundle\ApiBundle\Request\ApiClientInfo;
use DeskPRO\Bundle\ApiBundle\Security\Token\ApiKeySecurityToken;
use DeskPRO\Bundle\AppBundle\Form\Error\FormValidatorChecker;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Form\Type\DpHiddenType;
use DeskPRO\Bundle\AppBundle\Form\Type\HtmlTextareaType;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonAssignType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketAttachments\ApiTicketMessageAttachmentCollectionType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketAttachments\WebTicketMessageInlineAttachmentCollectionType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsApiType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsContext;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Component\Util\RegexUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormConfigBuilder;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TicketMessageType.
 */
class TicketMessageType extends AbstractType
{
    /**
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var ApiClientInfo
     */
    private $apiClientInfo;

    /**
     * Constructor.
     *
     * @param LanguageManager $languageManager
     * @param TokenStorage    $tokenStorage
     * @param ApiClientInfo   $apiClientInfo
     */
    public function __construct(LanguageManager $languageManager, TokenStorage $tokenStorage, ApiClientInfo $apiClientInfo = null)
    {
        $this->languageManager = $languageManager;
        $this->tokenStorage    = $tokenStorage;
        $this->apiClientInfo   = $apiClientInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('message', HtmlTextareaType::class, [
            'property_path' => 'message_html',
            'label'         => $options['message_label'],
            'required'      => $options['required'],
            'constraints'   => $options['message_constraints'],
        ]);

        if ($options['allow_set_person']) {
            $builder->add('person', PersonAssignType::class, [
                'mapped' => false,
            ]);
        }

        if ($options['format']) {
            $builder->add('format', DpHiddenType::class, [
                'empty_data' => 'hidden',
                'mapped'     => false,
            ]);
        } else {
            $builder->add('format', ChoiceType::class, [
                'mapped'            => false,
                'choices_as_values' => true,
                'choices'           => ['html', 'text'],
            ]);
        }

        $ticketMessage = $options['ticket_message'] ?: $builder->getData();
        if (!$ticketMessage) {
            // no message fallback
            $builder->setData(new TicketMessage());
            $ticketMessage = $builder->getData();
        }

        if ($options['render_is_note']) {
            $builder->add('is_note', ApiBooleanType::class, [
                'property_path' => 'is_agent_note',
            ]);
        }

        if ($options['has_attachments']) {
            // api
            $builder->add('attachments', ApiTicketMessageAttachmentCollectionType::class, [
                'required'       => false,
                'person'         => $options['person'],
                'ticket_message' => $ticketMessage,
            ]);
        } else {
            // web portal
            $builder->add('inline_attachments', WebTicketMessageInlineAttachmentCollectionType::class, [
                'required'       => false,
                'person'         => $options['person'],
                'ticket_message' => $ticketMessage,
                'mapped'         => false,
            ]);
        }

        if ($options['allow_set_status']) {
            $builder->add('status', ChoiceType::class, [
                'choices_as_values' => true,
                'choices'           => Ticket::getTicketStatuses(),
                'mapped'            => false,
                'required'          => false,
            ]);

            $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSetStatus'], 100);
        }
        if ($options['allow_apply_macros']) {
            $builder->add('macros', EntityType::class, [
                'class'         => TicketMacro::class,
                'multiple'      => true,
                'mapped'        => false,
                'required'      => false,
                'query_builder' => function (EntityRepository $repo) use ($options) {
                    return $repo
                        ->createQueryBuilder('e')
                        ->select('e')
                        ->andWhere('e.is_global = 1 OR e.person = :user_id')
                        ->setParameter('user_id', $options['person'])
                    ;
                },
            ]);

            $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onApplyMacros'], 100);
        }

        if ($options['with_ticket_validation']) {
            $builder->add('ticket', TicketWithLayoutsApiType::class, [
                'person'              => $options['person'],
                'ticket_view_context' => TicketWithLayoutsContext::VIEW_AGENT,
                'ticket_visibility'   => TicketWithLayoutsContext::VISIBILITY_EDIT,
                'disabled'            => true,
                'constraints'         => [
                    // don't add this constraint on the `ticket_message.ticket` property
                    // to allow to add replies to not valid ticket
                    new Assert\Valid(),
                ],
            ]);

            $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onValidateTicket']);
            $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onAccessTicketValidation'], 100);
        }

        if ($options['admin_api_key_request']) {
            $builder
                ->add('date_created', DateTimeType::class, [
                    'widget'   => 'single_text',
                    'required' => false,
                ])
            ;
        }

        $builder->addEventSubscriber(new TicketDisableAutoProcessListener());
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onEnsureMessageTextExists'], 100);
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onSetMessageFromOptions']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onChangeMessageFormat'], 100);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSetRelations'], 100);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'ensureAttachments'], 99);

        if ($this->apiClientInfo && $this->apiClientInfo->isIos()) {
            $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPurifyIosMessage']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'    => TicketMessage::class,
                'message_label' => $this->languageManager->phrase('portal.forms.label_message'),
                'attr'          => function (Options $options) {
                    return [
                        'data-rte'               => '1',
                        'data-ctrl-enter-submit' => (int) $options['ctrl_enter_submit'],
                    ];
                },
                'error_bubbling'         => false,
                'ticket'                 => null,
                'person'                 => null,
                'ticket_message'         => null,
                'render_is_note'         => true,
                'has_attachments'        => false,
                'format'                 => '',
                'with_ticket_validation' => false,
                'ctrl_enter_submit'      => false,
                'allow_set_person'       => $this->tokenStorage->getToken() instanceof ApiKeySecurityToken,
                'allow_set_status'       => false,
                'allow_apply_macros'     => false,
                'message_constraints'    => [],
                'error_mapping'          => [
                    // we use custom setters to modify message,
                    // so we need to map entity property with the form field
                    'message' => 'message',
                ],
                'constraints' => [
                    // check message directly via the form to prevent checking all ticket messages collection
                    new Assert\Valid(),
                ],
                'admin_api_key_request' => false,
            ])
            ->setRequired([
                'ticket',
                'person',
            ])
            ->setAllowedTypes('person', Person::class)
            ->setAllowedTypes('ticket', Ticket::class)
            ->setAllowedTypes('ticket_message', ['null', TicketMessage::class])
            ->setAllowedValues('format', ['', 'html', 'text'])
            ->setAllowedTypes('ctrl_enter_submit', 'bool')
            ->setAllowedTypes('allow_set_person', 'bool')
            ->setAllowedTypes('allow_set_status', 'bool')
            ->setAllowedTypes('allow_apply_macros', 'bool')
            ->setAllowedTypes('admin_api_key_request', 'bool')
        ;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onEnsureMessageTextExists(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        if (is_array($data) && !isset($data['message'])) {
            $data['message'] = '';
        }

        // add original message to handle agent note mentions
        $ticketMessage = $form->getData();
        if ($ticketMessage instanceof TicketMessage) {
            $ticketMessage->setOriginalMessage(isset($data['message']) ? $data['message'] : '');
        }

        $event->setData($data);
    }

    public function ensureAttachments(FormEvent $event)
    {
        $data = $event->getData();

        if (!$data || !($data instanceof TicketMessage)) {
            return;
        }

        /** @var TicketMessage $data */
        foreach ($data->getAttachments() as $attachment) {
            if ($attachment->isInline()) {
                $blob = $attachment->getBlob();

                $regex   = '#(<img[^>]+src=")'.preg_quote($blob->getDownloadUrl(true), '#').'("[^>]*>)#i';
                $matches = RegexUtils::safePregMatch($regex, $data->getMessageHtml());

                $regex   = '#<a[^>]+'.preg_quote('dp-embed-blob-a-'.$blob->getAuthId()).'[^>]*>.*?</a>#';
                $matches = $matches ?: RegexUtils::safePregMatch($regex, $data->getMessageHtml());

                $regex   = '#<img[^>]+'.preg_quote('dp-embed-blob-img-'.$blob->getAuthId()).'[^>]>#';
                $matches = $matches ?: RegexUtils::safePregMatch($regex, $data->getMessageHtml());

                if (!$matches) {
                    $blob->setIsTemp(true);
                    $data->removeAttachment($attachment);
                }
            }
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSetMessageFromOptions(FormEvent $event)
    {
        $config = $event->getForm()->getConfig();
        if ($config->getOption('ticket_message')) {
            $event->setData($config->getOption('ticket_message'));
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onChangeMessageFormat(FormEvent $event)
    {
        /** @var TicketMessage $data */
        $data = $event->getData();
        $form = $event->getForm();

        if (!$data || !($data instanceof TicketMessage)) {
            return;
        }

        if ($form->get('format')->getData() === 'text') {
            $data->setMessageText($data->convertEmbeddedImagesToInlineAttachInText($data->getMessageHtml()));
        } else {
            $data->setMessageHtml($data->convertEmbeddedImagesToInlineAttachInText($data->getMessageHtml()));
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSetRelations(FormEvent $event)
    {
        $form   = $event->getForm();
        $data   = $event->getData();
        $config = $form->getConfig();

        /** @var Ticket $ticket */
        $ticket = $config->getOption('ticket');

        if ($data instanceof TicketMessage) {
            if ($ticket && !$ticket->messages->contains($data)) {
                $ticket->addMessage($data);
            }

            $optionPerson = $config->getOption('person');
            if ($optionPerson) {
                $data->setPerson($optionPerson);
            }

            if ($form->has('person')) {
                $formPerson = $form->get('person')->getData();
                if ($formPerson) {
                    $data->setPerson($formPerson);
                }
            }
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onValidateTicket(FormEvent $event)
    {
        $form = $event->getForm()->get('ticket');

        foreach ($form->all() as $child) {
            FormValidatorChecker::submitForm($child);
        }
    }

    /**
     * We can't get form errors from disabled form so we need to enable the ticket form to access them.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onAccessTicketValidation(FormEvent $event)
    {
        $form   = $event->getForm()->get('ticket');
        $config = $form->getConfig();

        $property = new \ReflectionProperty(FormConfigBuilder::class, 'disabled');
        $property->setAccessible(true);
        $property->setValue($config, false);
        $property->setAccessible(false);
    }

    /**
     * The ios app sends html message in bad format so we need to 'fix' it before submit.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPurifyIosMessage(FormEvent $event)
    {
        $data    = $event->getData();
        $message = isset($data['message']) ? $data['message'] : '';

        $data['format']  = 'html';
        $data['message'] = nl2br($message);

        $event->setData($data);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSetStatus(FormEvent $event)
    {
        $form   = $event->getForm();
        $config = $form->getConfig();

        /** @var Ticket $ticket */
        $ticket = $config->getOption('ticket');
        $status = $form->get('status')->getData();
        if ($status) {
            $ticket->setStatus($status);
        }
    }

    /**
     * Apply a list of macros on reply submission.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onApplyMacros(FormEvent $event)
    {
        $form   = $event->getForm();
        $config = $form->getConfig();

        /** @var TicketMessage $data */
        $data = $event->getData();

        /** @var Ticket $ticket */
        $ticket = $config->getOption('ticket');
        $person = $config->getOption('person');
        $macros = $form->get('macros')->getData();

        if (!$macros instanceof ArrayCollection) {
            return;
        }

        foreach ($macros as $macro) {
            if (!$macro instanceof TicketMacro) {
                continue;
            }

            $actions = new ActionsCollection();
            foreach ($macro->getActionsCollection()->getActions() as $action) {
                // status actions
                if ($action instanceof StatusAction) {
                    continue;
                }

                // apply reply actions to the ticket message
                if ($action instanceof AbstractReplyAction) {
                    $actionContent = $action->getMessageContent($ticket);
                    if ($actionContent) {
                        if ($action->getReplyPos() === AbstractReplyAction::REPLY_POS_PREPEND) {
                            $contentParts = [$actionContent, $data->getMessageHtml()];
                        } elseif ($action->getReplyPos() === AbstractReplyAction::REPLY_POS_APPEND) {
                            $contentParts = [$data->getMessageHtml(), $actionContent];
                        } else {
                            $contentParts = [$actionContent];
                        }

                        $data->setMessageHtml(implode("\n<br/><br/>\n", $contentParts));
                    }
                } else {
                    $actions->add($action);
                }
            }

            $actions->apply($ticket->getTicketLogger(), $ticket, $person);
        }
    }
}
