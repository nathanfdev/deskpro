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

namespace Application\PortalBundle\EventListener;

use Application\DeskPRO\Brand\BrandStack;
use Application\DeskPRO\EntityRepository\Brand;
use Application\DeskPRO\Entity\Brand as BrandEntity;
use Application\DeskPRO\NewSettings\SettingsResolver;
use Application\PortalBundle\Annotation\TagOptions;
use Application\PortalBundle\Request\TagRequest;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\FileCacheReader;
use Doctrine\Common\Util\ClassUtils;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ExpressionLanguage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Early in a request, this listener will determine the active brand for this request and push it onto the brand_stack
 * service.
 */
class TagOptionsListener implements EventSubscriberInterface
{
    /**
     * @var ExpressionLanguage
     */
    protected $expressionLanguage;

    /**
     * @var AnnotationReader
     */
    private $reader;
    /**
     * @var Container
     */
    private $container;

    public function __construct(FileCacheReader $reader, Container $container)
    {
        $this->reader = $reader;
        $this->container = $container;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        /** @var \Application\PortalBundle\Request\TagRequest $tag_request */
        $tag_request = $event->getRequest();
        $request_backup = false;

        if (!$tag_request instanceof TagRequest) {
            if (!$tag_request->attributes->has('tag_request')) {
                return;
            }
            $request_backup = $tag_request;
            $tag_request = $tag_request->attributes->get('tag_request');
        }

        $className = ClassUtils::getClass($controller[0]);
        $object = new \ReflectionClass($className);
        $method = $object->getMethod($controller[1]);

        $annotations = $this->reader->getMethodAnnotations($method);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof TagOptions) {
                if (count($annotation->defaults)) {
                    $tag_request->getOptionsResolver()->setDefaults($annotation->defaults);
                }

                if (count($annotation->required)) {
                    $tag_request->getOptionsResolver()->setRequired($annotation->required);
                }

                if (count($annotation->allowed_types)) {
                    $tag_request->getOptionsResolver()->setAllowedTypes($annotation->allowed_types);
                }

                if (count($annotation->allowed_values)) {
                    $tag_request->getOptionsResolver()->setAllowedValues($annotation->allowed_values);
                }

                $resolved_tag_options = $tag_request->getTagOptions();

                $tag_request->attributes->set('options', $resolved_tag_options);
                if ($request_backup) {
                    $request_backup->attributes->set('options', $resolved_tag_options);
                }

                // run thru any expressions, evaluate them, and set them as tag_request attributes
                foreach ($annotation->attribute_expressions as $attribute => $expression) {
                    $tag_request->attributes->set($attribute, $this->evaluate($expression, array('options' => $resolved_tag_options)));
                }

                break; // we only care about finding one TagOptions here
            }
        }

    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => array('onKernelController', -10)
        );
    }

    protected function evaluate($expr, array $variables)
    {
        return $this->getExpressionLanguage()->evaluate($expr, array_merge($variables, array('container' => $this->container)));
    }

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
