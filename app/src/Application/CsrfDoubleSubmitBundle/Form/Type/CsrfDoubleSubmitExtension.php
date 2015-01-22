<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\CsrfDoubleSubmitBundle\Form\Type;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Exception;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeExtensionInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CsrfDoubleSubmitExtension extends AbstractTypeExtension
{
    /**
     * @var RequestStack
     */
    private $request_stack;

    public function __construct(RequestStack $request_stack)
    {
        // generally its not a good idea to make form's directly associated with a request object,
        // but in this case its the easiest way to access the cookie value
        $this->request_stack = $request_stack;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['csrf_double_submit_protection']) {
            return;
        }

        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if ($options['csrf_double_submit_protection'] && !$view->parent && $options['compound']) {
            $factory = $form->getConfig()->getFormFactory();

            $csrfForm = $factory->createNamed($options['csrf_double_submit_cookie_name'], 'hidden', '', array(
                'mapped' => false,
                'label' => false
            ));

            $view->children[$options['csrf_double_submit_cookie_name']] = $csrfForm->createView($view);
        }
    }

    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if ($form->isRoot() && $form->getConfig()->getOption('compound')) {
            $form_config = $form->getConfig();
            $cookie_name = $form_config->getOption('csrf_double_submit_cookie_name');
            $cookie_value = $this->request_stack->getMasterRequest()->cookies->get($cookie_name, null);

            // token must be present in submitted data, and exactly equal to the request cookie value
            // token MUST be at least 5 characters
            if (
                !isset($data[$cookie_name])
                || !$cookie_value
                || strlen($cookie_value) < 5
                || $data[$cookie_name] !== $cookie_value
            ) {
                $error_message = $form_config->getOption('csrf_double_submit_error_message');
                $form->addError(new FormError($error_message));
            }

            if (is_array($data)) {
                unset($data[$cookie_name]);
            }
        }

        $event->setData($data);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        // apply to all forms and use the new method of protection
        // calling-code can always turn this off and the other back on
        $resolver->setDefaults(
            array(
                'csrf_protection' => false,
                'csrf_double_submit_protection' => true,
                'csrf_double_submit_cookie_name' => '_dp_csrf_token',
                'csrf_double_submit_error_message' => 'You did not submit a valid token. For security reasons, please ensure javascript is enabled, and cookies are enabled.'
            )
        )->setAllowedTypes(
            array(
                'csrf_double_submit_protection' => 'bool',
                'csrf_double_submit_cookie_name' => 'string',
                'csrf_double_submit_error_message' => 'string'
            )
        );
    }


    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        return 'form';
    }
}