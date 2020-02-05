<?php

namespace DeskPRO\Bundle\MessengerBundle\Form\Type\Settings;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\AppBundle\Security\Permissions\PermissionsManager;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerChatTicketDefaults;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class MessengerChatTicketDefaultsType extends AbstractType
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
            ->add('subject', TextType::class)
            ->add('subjectType', ChoiceType::class, [
                'required' => true,
                'choices'  => [
                    MessengerChatTicketDefaults::MISSED_CHAT_TICKET_SUBJECT_TYPE_SET,
                    MessengerChatTicketDefaults::MISSED_CHAT_TICKET_SUBJECT_TYPE_AUTO,
                ],
                'choices_as_values' => true,
                'constraints'       => [
                    new Assert\NotNull(),
                ],

            ])
            ->add('department', EntityType::class, [
                'class'         => Department::class,
                'choice_label'  => 'id',
                'query_builder' => function (EntityRepository $er) use ($brand) {
                    $qb = $er
                        ->createQueryBuilder('d')
                        ->join('d.brands', 'b')
                        ->where(
                            'd.is_tickets_enabled = true',
                            'b.id IN(:brand)'
                        )
                        ->setParameter('brand', $brand)
                    ;

                    return $qb;
                },
                'required' => true,
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
                'data_class' => MessengerChatTicketDefaults::class,
            ])
            ->setRequired(['brand'])
            ->setAllowedTypes('brand', [Brand::class])
        ;
    }
}
