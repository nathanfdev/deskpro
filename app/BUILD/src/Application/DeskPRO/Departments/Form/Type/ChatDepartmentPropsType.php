<?php

namespace Application\DeskPRO\Departments\Form\Type;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\AppBundle\Entity\UserChatQueue;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ChatDepartmentPropsType.
 */
class ChatDepartmentPropsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => false,
            ])
            ->add('user_title', TextType::class, [
                'required' => false,
            ])
            ->add('parent', EntityType::class, [
                'class'         => 'DeskPRO:Department',
                'required'      => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er
                        ->createQueryBuilder('d')
                        ->where('d.is_chat_enabled = true AND d.parent IS NULL')
                        ->orderBy('d.display_order', 'ASC');
                },
            ])
            ->add('chat_queue', EntityType::class, [
                'class'         => UserChatQueue::class,
                'required'      => false,
                'property_path' => 'chatQueue',
            ])
            ->add('avatar', EntityType::class, [
                'required' => false,
                'class'    => 'DeskPRO:Blob',
            ])
            ->add('brands', EntityType::class, [
                'class'        => Brand::class,
                'required'     => false,
                'expanded'     => true,
                'multiple'     => true,
                'choice_label' => 'name',
                'by_reference' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Department::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'department';
    }
}
