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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CsrfDoubleSubmitExtension extends AbstractTypeExtension
{
    const COOKIE_NAME = '_dp_csrf_token';

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var string the kernel env (dev/test/prod)
     */
    private $environment;

    /**
     * @var LanguageManager
     */
    private $languageManager;

    public function __construct(RequestStack $requestStack, LanguageManager $languageManager, $environment)
    {
        // generally its not a good idea to make form's directly associated with a request object,
        // but in this case its the easiest way to access the cookie value
        $this->requestStack    = $requestStack;
        $this->environment     = $environment;
        $this->languageManager = $languageManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->shouldNotApply($options)) {
            return;
        }

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    protected function shouldNotApply(array $options)
    {
        if ('test' === $this->environment) {
            return true;
        }

        if (!$options['csrf_double_submit_protection']) {
            return true;
        }

        // don't add CSRF on the saved form requests
        if ($options['saved_form_subrequest']) {
            return true;
        }

        return false;
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        // if saved_form_subrequest is true, we should still add to the view, because
        // if the users sees the view it means there is an error and we need to process CSRF after that
        if ($this->shouldNotApply($options) && !$options['saved_form_subrequest']) {
            return;
        }

        if (!$view->parent && $options['compound']) {
            $factory = $form->getConfig()->getFormFactory();

            $csrfForm = $factory->createNamed(
                $options['csrf_double_submit_cookie_name'],
                'hidden',
                '',
                [
                    'mapped' => false,
                    'label'  => false,
                ]
            );

            $view->children[$options['csrf_double_submit_cookie_name']] = $csrfForm->createView($view);
        }
    }

    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if ($form->isRoot() && $form->getConfig()->getOption('compound')) {
            $formConfig  = $form->getConfig();
            $cookieName  = $formConfig->getOption('csrf_double_submit_cookie_name');
            $cookieValue = $this->requestStack->getCurrentRequest()->cookies->get($cookieName, null);

            // token must be present in submitted data, and exactly equal to the request cookie value
            // token MUST be at least 5 characters
            if (
                !isset($data[$cookieName])
                || !$cookieValue
                || strlen($cookieValue) < 5
                || $data[$cookieName] !== $cookieValue
            ) {
                $errorMessage = null;
                if ($lang = $this->languageManager->getLanguageStack()->getActive()) {
                    $errorMessage = $this->languageManager->getTranslator($lang)->phrase(
                        $formConfig->getOption('csrf_double_submit_error_message')
                    );
                }
                if (!$errorMessage) {
                    $errorMessage = $formConfig->getOption('csrf_double_submit_error_message');
                }

                $form->addError(new FormError($errorMessage));
            }

            if (is_array($data)) {
                unset($data[$cookieName]);
            }
        }

        $event->setData($data);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        // apply to all forms and use the new method of protection
        // calling-code can always turn this off and the other back on
        $resolver->setDefaults(
            [
                'csrf_protection'                  => false,
                'csrf_double_submit_protection'    => true,
                'csrf_double_submit_cookie_name'   => self::COOKIE_NAME,
                'csrf_double_submit_error_message' => 'portal.forms.error_csrf',
            ]
        )->setAllowedTypes(
            [
                'csrf_double_submit_protection'    => 'bool',
                'csrf_double_submit_cookie_name'   => 'string',
                'csrf_double_submit_error_message' => 'string',
            ]
        );
    }

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        return FormType::class;
    }
}
