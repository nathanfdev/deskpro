<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DepartmentType.
 */
class DepartmentType extends AbstractType
{
    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * Constructor.
     *
     * @param BrandStack $brandStack
     */
    public function __construct(BrandStack $brandStack)
    {
        $this->brandStack = $brandStack;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
            ])
            ->add('user_title', TextType::class, [
                'required' => false,
            ])
            ->add('parent', EntityType::class, [
                'class'         => Department::class,
                'required'      => false,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    $typeProperty = $options['type'] === 'tickets' ? 'is_tickets_enabled' : 'is_chat_enabled';

                    return $er
                        ->createQueryBuilder('d')
                        ->where("d.$typeProperty = true AND d.parent IS NULL")
                        ->orderBy('d.display_order', 'ASC')
                    ;
                },
            ])
            ->add('avatar', BlobAuthType::class, [
                'required' => false,
            ])
            ->add('display_order', IntegerType::class, [
                'empty_data' => '0',
                'required'   => false,
            ])
            ->add('brands', EntityType::class, [
                'class'    => Brand::class,
                'multiple' => true,
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSetType']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['type'])
            ->setDefaults([
                'data_class' => Department::class,
            ])
            ->setAllowedValues('type', ['tickets', 'chat'])
        ;
    }

    /**
     * @param FormEvent $event
     */
    public function onSetType(FormEvent $event)
    {
        $form = $event->getForm();
        $type = $form->getConfig()->getOption('type');

        /** @var Department $data */
        $data = $form->getData();

        if ($type === 'tickets') {
            $data->setIsTicketsEnabled(true);
            $data->setIsChatEnabled(false);
        } else {
            $data->setIsTicketsEnabled(false);
            $data->setIsChatEnabled(true);
        }

        if (!count($data->getBrands())) {
            $defaultBrand = $this->brandStack->getDefaultBrand();
            if ($defaultBrand) {
                $data->getBrands()->add($defaultBrand);
            }
        }
    }
}
