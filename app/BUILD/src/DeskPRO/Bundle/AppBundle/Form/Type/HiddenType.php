<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
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
class HiddenType extends AbstractType
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
    public function getName()
    {
        return 'deskpro_hidden';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'hidden';
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
