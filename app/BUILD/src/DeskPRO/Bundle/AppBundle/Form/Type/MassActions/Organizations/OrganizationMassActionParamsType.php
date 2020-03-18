<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\MassActions\Organizations;

use Application\DeskPRO\Entity\Organization;
use DeskPRO\Bundle\AppBundle\Form\Type\MassActions\BaseMassActionParamsType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OrganizationMassActionParamsType.
 */
class OrganizationMassActionParamsType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit'], 100);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'actions'    => ['delete'],
            'data_class' => Organization::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return BaseMassActionParamsType::class;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (!$data instanceof Organization) {
            return;
        }

        if (BaseMassActionParamsType::hasAction($event, 'delete')) {
            $this->em->remove($data);
        }
    }
}
