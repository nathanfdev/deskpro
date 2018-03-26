<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\Widget\BrandSettings;

use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\AppBundle\Form\DataTransformer\EntityToIdTransformer;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\WidgetBrandTicketSettings;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\ReversedTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class WidgetBrandTicketSettingsType.
 */
class WidgetBrandTicketSettingsType extends AbstractType
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
            ->add('select_department', ChoiceType::class, [
                'property_path'     => 'selectDepartment',
                'choices_as_values' => true,
                'choices'           => [
                    WidgetBrandTicketSettings::SELECT_DEFAULT,
                    WidgetBrandTicketSettings::SELECT_CUSTOM,
                ],
            ])
            ->add('default_department', EntityType::class, [
                'property_path' => 'defaultDepartment',
                'class'         => Department::class,
            ])
            ->add('select_subject', ChoiceType::class, [
                'property_path'     => 'selectSubject',
                'choices_as_values' => true,
                'choices'           => [
                    WidgetBrandTicketSettings::SELECT_CUSTOM,
                    WidgetBrandTicketSettings::SELECT_MESSAGE,
                    WidgetBrandTicketSettings::SELECT_DEFAULT,
                ],
            ])
            ->add('default_subject', TextType::class, [
                'property_path' => 'defaultSubject',
            ])
        ;

        $departmentRepo = $this->em->getRepository(Department::class);
        $builder
            ->get('default_department')
            ->addModelTransformer(new ReversedTransformer(new EntityToIdTransformer($departmentRepo)))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WidgetBrandTicketSettings::class,
        ]);
    }
}
