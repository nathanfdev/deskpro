<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Departments\Form\Type;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Department;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketDepartmentPropsType extends AbstractType
{
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
                    'class'         => Department::class,
                    'required'      => false,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('d')->where('d.is_tickets_enabled = true AND d.parent IS NULL')->orderBy('d.display_order', 'ASC');
                    },
            ])
            ->add('avatar', TextType::class, [
                    'required' => false,
                    'mapped'   => false,
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

        /** @var \Application\DeskPRO\EntityRepository\Brand $brandRepos */
        $brandRepos = App::$container->getEm()->getRepository(Brand::class);

        // The brands select is hidden on dep edit form if there's only 1 brand defined
        // This makes sure that if (for some reason) no brand is already set on the dep that
        // the single brand is set
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($brandRepos) {
            $data = $event->getData();
            if (empty($data['brands'])) {
                /** @var Brand[] $brands */
                $brands = $brandRepos->findAll();
                if (count($brands) === 1) {
                    $data['brands'] = [$brands[0]->getId()];
                    $event->setData($data);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Department::class,
        ]);
    }
}
