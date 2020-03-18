<?php

namespace DeskPRO\Bundle\MessengerBundle\Form\Type\Settings;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Security\Permissions\PermissionsManager;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerChat;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class MessengerChatType.
 */
class MessengerChatType extends AbstractType
{
    /**
     * @var PermissionsManager
     */
    private $permissionsManager;

    /**
     * MessengerChatType constructor.
     *
     * @param PermissionsManager $permissionsManager
     */
    public function __construct(PermissionsManager $permissionsManager)
    {
        $this->permissionsManager = $permissionsManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $brand = $options['brand'];

        $builder
            ->add('enabled', ApiBooleanType::class)
            ->add('department', EntityType::class, [
                'class'         => Department::class,
                'choice_label'  => 'id',
                'query_builder' => function (EntityRepository $er) use ($brand) {
                    $qb = $er
                        ->createQueryBuilder('d')
                        ->join('d.brands', 'b')
                        ->where(
                            'd.is_chat_enabled = true',
                            'b.id IN(:brand)'
                        )
                        ->setParameter('brand', $brand)
                    ;

                    return $qb;
                },
                'required' => true,
            ])
            ->add('usergroups', CollectionType::class, [
                'property_path' => 'usergroups',
                'type'          => NumberType::class,
                'allow_add'     => true,
                'allow_delete'  => true,
            ])
            ->add('prompt', TextType::class)
            ->add('options', MessengerChatOptionsType::class)
            ->add('preChatForm', MessengerPreChatFormType::class)
            ->add('timeout', NumberType::class)
            ->add('noAnswerBehavior', ChoiceType::class, [
                'required' => true,
                'choices'  => [
                    MessengerChat::NO_ANSWER_SAVE_TICKET,
                    MessengerChat::NO_ANSWER_CREATE_TICKET,
                    MessengerChat::NO_ANSWER_SHOW_BUSY,
                ],
                'choices_as_values' => true,
                'constraints'       => [
                    new Assert\NotNull(),
                ],

            ])
            ->add('busyMessage', TextType::class)
            ->add('ticketDefaults', MessengerChatTicketDefaultsType::class, ['brand' => $brand])

        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => MessengerChat::class,
            ])
            ->setRequired(['brand'])
            ->setAllowedTypes('brand', [Brand::class])
        ;
    }
}
