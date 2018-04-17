<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Usersource;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Usersource;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UsersourceType.
 */
class UsersourceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // only user context can have brands
        if ($options['context'] === 'user') {
            $builder
                ->add('is_all_brands', ApiBooleanType::class, [
                    'required' => false,
                ])
                ->add('brands', EntityType::class, [
                    'required' => false,
                    'class'    => Brand::class,
                    'multiple' => true,
                ])
            ;
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Usersource::class,
            ])
            ->setRequired('context')
            ->setAllowedValues('context', ['agent', 'user'])
        ;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $data    = $event->getData();
        $options = $event->getForm()->getConfig()->getOptions();

        if ($data instanceof Usersource) {
            $data->setType($options['context']);

            // agent usersources are not brand dependant
            if ($data->getType() === 'agent') {
                $data->setIsAllBrands(true);
            }

            // unset specific brands if 'is_all_brands' is on
            if ($data->isAllBrands()) {
                foreach ($data->getBrands() as $brand) {
                    $data->removeBrand($brand);
                }
            }
        }

        $event->setData($data);
    }
}
