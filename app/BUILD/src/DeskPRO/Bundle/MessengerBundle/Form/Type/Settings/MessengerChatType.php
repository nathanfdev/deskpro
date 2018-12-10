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
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class MessengerEmbedType.
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
        $permissionsBag       = $this->permissionsManager->getPortalPermissionsBag();
        $allowedDepartmentIds = $permissionsBag->getAllowedChatDepartmentIds();
        $brand                = $options['brand'];

        $builder
            ->add('enabled', ApiBooleanType::class)
            ->add('prompt', TextType::class)
            ->add('busyMessage', TextType::class)
            ->add('timeout', NumberType::class)
            ->add('department', EntityType::class, [
                'class'         => Department::class,
                'choice_label'  => 'id',
                'query_builder' => function (EntityRepository $er) use ($allowedDepartmentIds, $brand) {
                    $qb = $er
                        ->createQueryBuilder('d')
                        ->join('d.brands', 'b')
                        ->where(
                            'd.is_chat_enabled = true',
                            'd.id IN (:allowed_department_ids)',
                            'b.id IN(:brand)'
                        )
                        ->setParameter('allowed_department_ids', $allowedDepartmentIds)
                        ->setParameter('brand', $brand)
                    ;

                    return $qb;
                },
                'required' => true,
            ])
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
            ->add('ticketSubject', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
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
