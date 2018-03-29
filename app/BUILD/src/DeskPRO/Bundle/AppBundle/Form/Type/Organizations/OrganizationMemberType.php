<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Organizations;

use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class OrganizationMemberType.
 */
class OrganizationMemberType extends AbstractType
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
        $builder
            ->add('person', EntityType::class, [
                'mapped'      => false,
                'required'    => true,
                'class'       => Person::class,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('position', TextType::class, [
                'property_path' => 'organization_position',
                'required'      => false,
            ])
        ;

        $builder->get('person')->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Person::class,
            ])
            ->setRequired('organization')
            ->setAllowedTypes('organization', Organization::class)
        ;
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $parent = $event->getForm()->getParent();
        $config = $parent->getConfig();
        $data   = $parent->getData();

        if ($data instanceof Person) {
            $data->setOrganization($config->getOption('organization'));
        }
    }
}
