<?php

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Extension;

use DeskPRO\Bundle\AppBundle\Form\Error\ErrorsCodes;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CsrfDoubleSubmitExtension.
 */
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
     * Constructor.
     *
     * @param RequestStack $requestStack
     * @param string       $environment
     */
    public function __construct(RequestStack $requestStack, $environment)
    {
        // generally its not a good idea to make form's directly associated with a request object,
        // but in this case its the easiest way to access the cookie value
        $this->requestStack = $requestStack;
        $this->environment  = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->shouldNotApply($options)) {
            return;
        }

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    /**
     * {@inheritdoc}
     */
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
                HiddenType::class,
                '',
                [
                    'mapped' => false,
                    'label'  => false,
                ]
            );

            $view->children[$options['csrf_double_submit_cookie_name']] = $csrfForm->createView($view);
        }
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $form    = $event->getForm();
        $data    = $event->getData();
        $options = $form->getConfig()->getOptions();

        if ($form->isRoot() && $options['compound'] && !$options['csrf_double_submit_skip_check']) {
            $cookieName  = $options['csrf_double_submit_cookie_name'];
            $cookieValue = $this->requestStack->getCurrentRequest()->cookies->get($cookieName, null);

            // token must be present in submitted data, and exactly equal to the request cookie value
            // token MUST be at least 5 characters
            if (
                !isset($data[$cookieName])
                || !$cookieValue
                || strlen($cookieValue) < 5
                || $data[$cookieName] !== $cookieValue
            ) {
                $form->addError(new FormError($options['csrf_double_submit_error_code']));
            }

            if (is_array($data)) {
                unset($data[$cookieName]);
            }
        }

        $event->setData($data);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        // apply to all forms and use the new method of protection
        // calling-code can always turn this off and the other back on
        $resolver
            ->setDefaults([
                'csrf_protection'                => false,
                'csrf_double_submit_protection'  => true,
                'csrf_double_submit_cookie_name' => self::COOKIE_NAME,
                'csrf_double_submit_error_code'  => ErrorsCodes::CSRF,
                'csrf_double_submit_skip_check'  => false,
            ])
            ->setAllowedTypes('csrf_double_submit_protection', 'bool')
            ->setAllowedTypes('csrf_double_submit_cookie_name', 'string')
            ->setAllowedTypes('csrf_double_submit_error_code', 'string')
            ->setAllowedTypes('csrf_double_submit_skip_check', 'bool')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return FormType::class;
    }

    /**
     * @param array $options
     *
     * @return bool
     */
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
}
