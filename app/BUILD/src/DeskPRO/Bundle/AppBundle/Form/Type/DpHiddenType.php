<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class HiddenType.
 */
class DpHiddenType extends AbstractType
{
    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $request_stack;

    /**
     * Constructor.
     *
     * @param RequestStack $request_stack
     */
    public function __construct(RequestStack $request_stack)
    {
        $this->request_stack = $request_stack;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPostSetData']);
    }

    /**
     * @param FormEvent $event
     */
    public function onPostSetData(FormEvent $event)
    {
        $config = $event->getForm()->getConfig();

        // if theres no data on the form and we want to auto fill, then proceed
        if (null === $config->getData() && $config->getOption('auto_fill')) {
            $request = $this->request_stack->getCurrentRequest();

            $cookie_name = $config->getOption('cookie_param_name');
            if ($cookie_name) {
                if ($request->cookies->has($cookie_name)) {
                    $event->setData($request->cookies->get($cookie_name));
                }
            }

            $request_name = $config->getOption('request_param_name');
            if ($request_name) {
                if ($request->query->has($request_name)) {
                    $event->setData($request->query->get($request_name));
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['hidden'] = (bool) $options['hidden'];
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return HiddenType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'auto_fill'          => false,
            'label'              => false,
            'help'               => false,
            'hidden'             => true,
            'request_param_name' => null,
            'cookie_param_name'  => null,
        ]);
    }
}
