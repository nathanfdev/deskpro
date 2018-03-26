<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Content;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Guide;
use DeskPRO\Bundle\AppBundle\Form\BrandFormHelper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class GuideType.
 */
class GuideType extends AbstractType
{
    /**
     * @var BrandFormHelper
     */
    private $brandHelper;

    /**
     * Constructor.
     *
     * @param BrandFormHelper $helper
     */
    public function __construct(BrandFormHelper $helper)
    {
        $this->brandHelper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('display_order', TextType::class)
            ->add('brand', EntityType::class, [
                'class' => Brand::class,
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
            'data_class' => Guide::class,
        ]);
    }

    /**
     * Assign manual to the current brand.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();

        /** @var Guide $data */
        $data = $form->getData();
        if (!$data->getBrand()) {
            $data->setBrand($this->brandHelper->getCurrentBrand());
        }
    }
}
