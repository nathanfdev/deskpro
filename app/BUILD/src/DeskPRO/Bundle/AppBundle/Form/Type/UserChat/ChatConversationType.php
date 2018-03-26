<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\UserChat;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Form\Type\CombinedType;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomDataType;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonAssignType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ChatConversationType.
 */
class ChatConversationType extends AbstractType
{
    /**
     * @var CustomFieldManager
     */
    private $fieldManager;

    /**
     * Constructor.
     *
     * @param CustomFieldManager $fieldManager
     */
    public function __construct(CustomFieldManager $fieldManager)
    {
        $this->fieldManager = $fieldManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', TextType::class, [
                'required' => false,
            ])
            ->add('person', PersonAssignType::class, [
                'required' => false,
            ])
            ->add('person_email', EmailType::class, [
                'required' => false,
            ])
            ->add('agent', PersonAssignType::class, [
                'required' => false,
            ])
            ->add('email_validated', ApiBooleanType::class, [
                'required' => false,
            ])
            ->add('fields', CombinedType::class, [
                'forms'          => $this->getCustomDataFields(),
                'error_bubbling' => false,
                'required'       => false,
            ])
            ->add('chat_department', EntityType::class, [
                'class'         => Department::class,
                'property_path' => 'department',
                'required'      => true,
                'query_builder' => function (EntityRepository $er) {
                    $qb = $er
                        ->createQueryBuilder('d')
                        ->join('d.brands', 'b')
                        ->where(
                            'd.is_chat_enabled = true'
                        )
                    ;

                    return $qb;
                },
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ChatConversation::class,
        ]);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();

        /** @var ChatConversation $conversation */
        $conversation = $form->getData();

        if ($conversation->getAgent()) {
            $conversation->addParticipant($conversation->getAgent());
        }
    }

    /**
     * @return array
     */
    private function getCustomDataFields()
    {
        $defs   = $this->fieldManager->getAvailableChatDefs();
        $fields = [];

        foreach ($defs as $def) {
            $fields[] = [
                'name'    => $def->getId(),
                'type'    => CustomDataType::class,
                'options' => [
                    'custom_def'      => $def,
                    'property_path'   => 'custom_data',
                    'agent_interface' => false,
                    'inline'          => true,
                ],
            ];
        }

        return $fields;
    }
}
