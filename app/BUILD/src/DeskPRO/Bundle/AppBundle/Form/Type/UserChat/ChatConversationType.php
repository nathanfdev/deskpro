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
use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Form\Type\CombinedType;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomDataType;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonAssignType;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\LeafDepartment;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

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

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', TextType::class, [

            ])
            ->add('person', PersonAssignType::class)
            ->add('person_email', EmailType::class)
            ->add('agent', PersonAssignType::class)
            ->add('email_validated', ApiBooleanType::class)
            ->add('fields', CombinedType::class, [
                'forms'          => $this->getCustomDataFields(),
                'error_bubbling' => false,
            ])
        ;

        $builder->add('chat_department', EntityType::class, [
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
            'constraints' => [
                new Assert\NotNull(),
                new LeafDepartment(),
            ],
        ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSetRelations']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => ChatConversation::class,
            ])
        ;
    }

    /**
     * @param FormEvent $event
     */
    public function onSetRelations(FormEvent $event)
    {
        $form = $event->getForm();

        if ($form->isValid()) {
            /** @var ChatConversation $conversation */
            $conversation = $form->getData();

            if ($conversation->getAgent()) {
                $conversation->addParticipant($conversation->getAgent());
            }
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
