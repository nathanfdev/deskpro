<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Content;

use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use DeskPRO\Bundle\AppBundle\Form\Type\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class NewsType.
 */
class NewsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category', EntityType::class, [
                'class'    => NewsCategory::class,
                'required' => false,
            ])
            ->add('date_published', DateTimeType::class, [
                'property_path' => 'date_published',
                'widget'        => 'single_text',
                'required'      => false,
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => News::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ContentAbstractType::class;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();

        // disable `date_published` in some cases, so it will be set in News entity depending from Status
        if (!isset($data['date_published']) || !$data['date_published']) {
            $this->disableDatePublished($event->getForm());
        }
    }

    /**
     * @param FormInterface $form
     */
    protected function disableDatePublished(FormInterface $form)
    {
        $form->add('date_published', DateTimeType::class, [
            'property_path' => 'date_published',
            'widget'        => 'single_text',
            'required'      => false,
            'disabled'      => true,
        ]);
    }
}
