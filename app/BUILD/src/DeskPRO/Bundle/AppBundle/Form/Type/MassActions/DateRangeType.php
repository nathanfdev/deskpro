<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\MassActions;

use DeskPRO\Bundle\AppBundle\Form\Type\DateTimeType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;

/**
 * Class DateRangeType.
 */
class DateRangeType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('min', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('max', DateTimeType::class, [
                'widget' => 'single_text',
            ])
        ;

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit'], -1);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['class', 'date_property'])
            ->setDefaults([
                'error_bubbling' => false,
                'max_entities'   => null,
            ])
        ;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        $options = $event->getForm()->getConfig()->getOptions();
        $count   = $this->getCount($event);
        if (!$count || $options['max_entities'] && $count > $options['max_entities']) {
            $event->setData([]);
            return;
        }

        $qb = $this->createQb($event);
        $qb->select('e');

        $result = $qb->getQuery()->getResult();
        $event->setData($result);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();

        $options = $form->getConfig()->getOptions();
        $count   = $this->getCount($event);
        if ($options['max_entities'] && $count > $options['max_entities']) {
            $form->addError(new FormError(
                Count::TOO_MANY_ERROR,
                null,
                [
                    '{{ count }}' => $count,
                    '{{ limit }}' => $options['max_entities'],
                ]
            ));
        }
    }

    /**
     * @param FormEvent $event
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function createQb(FormEvent $event)
    {
        $form = $event->getForm();
        $min  = $form->get('min')->getData();
        $max  = $form->get('max')->getData();

        $options = $form->getConfig()->getOptions();

        $qb = $this->em->createQueryBuilder();
        $qb->from($options['class'], 'e');

        if ($min) {
            $qb->andWhere('e.'.$options['date_property'].' >= :min');
            $qb->setParameter('min', $min);
        }
        if ($max) {
            $qb->andWhere('e.'.$options['date_property'].' <= :max');
            $qb->setParameter('max', $max);
        }

        return $qb;
    }

    /**
     * @param FormEvent $event
     *
     * @return int
     */
    private function getCount(FormEvent $event)
    {
        $countQb = $this->createQb($event);
        $countQb->select('count(e)');

        if (!$countQb->getDQLPart('where')) {
            return 0;
        }

        return $countQb->getQuery()->getSingleScalarResult();
    }
}
