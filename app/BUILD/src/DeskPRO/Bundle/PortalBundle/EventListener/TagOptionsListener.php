<?php

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use DeskPRO\Bundle\AppBundle\HttpKernel\SkipLowRequestInterface;
use DeskPRO\Bundle\PortalBundle\Annotation\TagOptions;
use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ExpressionLanguage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Early in a request, this listener will determine the active brand for this request and push it onto the brand_stack
 * service.
 */
class TagOptionsListener implements EventSubscriberInterface, SkipLowRequestInterface
{
    /**
     * @var ExpressionLanguage
     */
    protected $expressionLanguage;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var Container
     */
    private $container;

    /**
     * Constructor.
     *
     * @param Reader    $reader
     * @param Container $container
     */
    public function __construct(Reader $reader, Container $container)
    {
        $this->reader    = $reader;
        $this->container = $container;
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        /** @var \DeskPRO\Bundle\PortalBundle\Request\TagRequest $tag_request */
        $tag_request    = $event->getRequest();
        $request_backup = false;

        if (!$tag_request instanceof TagRequest) {
            if (!$tag_request->attributes->has('tag_request')) {
                return;
            }

            $request_backup = $tag_request;
            $tag_request    = $tag_request->attributes->get('tag_request');
        }

        $className = ClassUtils::getClass($controller[0]);
        $object    = new \ReflectionClass($className);
        $method    = $object->getMethod($controller[1]);

        $annotations     = $this->reader->getMethodAnnotations($method);
        $optionsResolver = $tag_request->getOptionsResolver();

        foreach ($annotations as $annotation) {
            if ($annotation instanceof TagOptions) {
                if (count($annotation->defaults)) {
                    $optionsResolver->setDefaults($annotation->defaults);
                }
                if (count($annotation->required)) {
                    $optionsResolver->setRequired($annotation->required);
                }
                if (count($annotation->allowed_types)) {
                    foreach ($annotation->allowed_types as $name => $types) {
                        $optionsResolver->setAllowedTypes($name, $types);
                    }
                }
                if (count($annotation->allowed_values)) {
                    foreach ($annotation->allowed_values as $name => $values) {
                        $optionsResolver->setAllowedValues($name, $values);
                    }
                }

                $resolved_tag_options = $tag_request->getTagOptions($annotation->inherit_from);

                $tag_request->attributes->set('options', $resolved_tag_options);
                if ($request_backup) {
                    $request_backup->attributes->set('options', $resolved_tag_options);
                }

                // run thru any expressions, evaluate them, and set them as tag_request attributes
                foreach ($annotation->attribute_expressions as $attribute => $expression) {
                    $tag_request->attributes->set($attribute, $this->evaluate($expression, ['options' => $resolved_tag_options]));
                }

                break; // we only care about finding one TagOptions here
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', -10],
        ];
    }

    /**
     * @param mixed $expr
     * @param array $variables
     *
     * @return string
     */
    protected function evaluate($expr, array $variables)
    {
        return $this->getExpressionLanguage()->evaluate($expr, array_merge($variables, ['container' => $this->container]));
    }

    /**
     * @return ExpressionLanguage
     */
    protected function getExpressionLanguage()
    {
        if (null === $this->expressionLanguage) {
            if (!class_exists('Symfony\Component\ExpressionLanguage\ExpressionLanguage')) {
                throw new \RuntimeException('Unable to use expressions as the Symfony ExpressionLanguage component is not installed.');
            }
            $this->expressionLanguage = new ExpressionLanguage();
        }

        return $this->expressionLanguage;
    }
}
