<?php

namespace DeskPRO\Bundle\ReportBundle\Form\Type;

use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlCompiler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DpqlPartsType.
 */
class DpqlPartsType extends AbstractType
{
    /**
     * @var DpqlCompiler
     */
    private $compiler;

    /**
     * Constructor.
     *
     * @param DpqlCompiler $compiler
     */
    public function __construct(DpqlCompiler $compiler)
    {
        $this->compiler = $compiler;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('select', TextType::class, [
                'required' => true,
            ])
            ->add('from', TextType::class, [
                'required' => true,
            ])
            ->add('where', TextType::class, [
                'required' => true,
            ])
            ->add('split_by', TextType::class, [
                'required' => true,
            ])
            ->add('group_by', TextType::class, [
                'required' => true,
            ])
            ->add('with_rollup', ApiBooleanType::class, [
                'required' => true,
            ])
            ->add('order_by', TextType::class, [
                'required' => true,
            ])
            ->add('limit', TextType::class, [
                'required' => true,
            ])
            ->add('offset', TextType::class, [
                'required' => true,
            ])
        ;

        $builder->addViewTransformer(new DpqlPartsTransformer($this->compiler, $options['variables']));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('variables')
            ->setAllowedTypes('variables', ['array', 'null'])
            ->setDefaults(['variables' => []])
        ;
    }
}
