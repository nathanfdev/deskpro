<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\MassActions;

use DeskPRO\Bundle\AppBundle\Form\Type\DateTimeType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
        $form = $event->getForm();
        $data = $event->getData();

        $options = $form->getConfig()->getOptions();

        $min = isset($data['min']) && $data['min'] instanceof \DateTime ? $data['min'] : null;
        $max = isset($data['max']) && $data['max'] instanceof \DateTime ? $data['max'] : null;

        if ($min || $max) {
            $qb = $this->em->createQueryBuilder();
            $qb
                ->select('e')
                ->from($options['class'], 'e')
            ;

            if ($min) {
                $qb->andWhere('e.'.$options['date_property'].' >= :min');
                $qb->setParameter('min', $min);
            }
            if ($max) {
                $qb->andWhere('e.'.$options['date_property'].' <= :max');
                $qb->setParameter('max', $max);
            }

            $result = $qb->getQuery()->getResult();
            $event->setData($result);
        } else {
            $event->setData([]);
        }
    }
}
