<?php


namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;

/**
 * Class LimitEntityType.
 */
class EntityWithMaxLimitType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return EntityType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('max_entities')
            ->setDefaults([
                'multiple'      => true,
                'query_builder' => function (EntityRepository $er, $options) {
                    $qb = $er->createQueryBuilder('c');
                    if ($options['max_entities']) {
                        $qb->setMaxResults($options['max_entities']);
                    }

                    return $qb;
                },
            ])
            ->setNormalizer('query_builder', function (OptionsResolver $options, $qb) {
                return call_user_func($qb, $options['em']->getRepository($options['class']), $options);
            });
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $options = $form->getConfig()->getOptions();
        $count   = count($data);
        if ($options['max_entities'] && $count > $options['max_entities']) {
            $form->addError(new FormError(
                Count::TOO_MANY_ERROR,
                null,
                [
                    '{{ count }}' => $count,
                    '{{ limit }}' => $options['max_entities'],
                ]
            ));

            $property = new \ReflectionProperty(Form::class, 'viewData');
            $property->setAccessible(true);
            $property->setValue($form, array_slice($form->getViewData(), 0, $options['max_entities']));
            $property->setAccessible(false);

            $property = new \ReflectionProperty(Form::class, 'transformationFailure');
            $property->setAccessible(true);
            $property->setValue($form, null);
            $property->setAccessible(false);
        }
    }
}
