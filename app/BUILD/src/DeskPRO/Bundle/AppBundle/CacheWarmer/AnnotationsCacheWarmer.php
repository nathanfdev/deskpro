<?php

namespace DeskPRO\Bundle\AppBundle\CacheWarmer;

use Application\AdminInterfaceBundle\Controller\IndexController;
use Application\AgentBundle\Controller\InterfaceController;
use Application\AgentBundle\Controller\LabelsController;
use Application\AgentBundle\Controller\MiscController;
use Application\AgentBundle\Controller\PeopleSearchController;
use Application\AgentBundle\Controller\PersonController;
use Application\AgentBundle\Controller\PublishController;
use Application\AgentBundle\Controller\TaskController;
use Application\AgentBundle\Controller\TicketSearchController;
use Application\AgentBundle\Controller\UserChatController;
use Application\DeskPRO\Entity\Labels\LabelsOwner;
use DeskPRO\Bundle\AppBundle\Entity\CustomPerDataOwnerInterface;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\MessengerBundle\Controller\ExceptionController;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketNewByAgent;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\NotifyPropertyChanged;
use FOS\ElasticaBundle\Transformer\HighlightableModelInterface;
use Orb\Util\OptionsArray;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Util\OrderedHashMap;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * This finds all FQCN in the project, and asks the annotation reader for its annotations.
 *
 * This will force the Annotation FileCacheReader to save the annotations to disk in the cache for every class
 * in the project. This avoids any attempt to write to the cache when we try to get annotations for a class because
 * the annotations are already cached.
 */
class AnnotationsCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return true; // we dont want to do this in dev env
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $annotationRegex = '/@[A-Z]+/';

        $dirs = [
            DP_ROOT.'/src/Application',
            DP_ROOT.'/src/DeskPRO',
        ];

        $annotaionReader = $this->container->get('annotation_reader');
        $finder          = Finder::create()
            ->in($dirs)
            ->name('*.php')
            ->notName('*.txt.php')
            ->notPath('/Dpql\/build.+/')
            ->notPath('/Resources/')
            ->notPath('/InstallBundle\/Data/')
            ->contains($annotationRegex)
        ;

        foreach ($finder as $f) {
            require_once $f->getRealPath();
        }

        $classes = get_declared_classes();

        $extraClasses = [
            Form::class,
            ExceptionController::class,
            \IteratorAggregate::class,
            \Traversable::class,
            FormInterface::class,
            \ArrayAccess::class,
            \Countable::class,
            NotifyPropertyChanged::class,
            HighlightableModelInterface::class,
            LabelsOwner::class,
            CustomPerDataOwnerInterface::class,
            EntityInterface::class,
            ArrayCollection::class,
            Collection::class,
            Selectable::class,
            OrderedHashMap::class,
            MiscController::class,
            TicketSearchController::class,
            UserChatController::class,
            PeopleSearchController::class,
            PublishController::class,
            TaskController::class,
            LabelsController::class,
            OptionsArray::class,
            PersonController::class,
            TicketNewByAgent::class,
            InterfaceController::class,
            \Application\AdminInterfaceBundle\Controller\InterfaceController::class,
            IndexController::class,
            RedirectController::class,
        ];

        foreach (array_merge($classes, $extraClasses) as $class) {

            if (0 !== strpos($class, 'DeskPRO') && 0 !== strpos($class, 'Application') && !in_array($class, $extraClasses)) {
                continue;
            }

            $reflection = new \ReflectionClass($class);

            if (!preg_match($annotationRegex, (string) @file_get_contents($reflection->getFileName())) && !in_array($class, $extraClasses)) {
                continue;
            }

            try {
                $annotaionReader->getClassAnnotations($reflection);

            } catch (AnnotationException $e) {
                // todo we have lots of @option that throw AnnotationException. ignore or cleanup?
            }

            foreach ($reflection->getProperties() as $propRef) {
                try {
                    $annotaionReader->getPropertyAnnotations($propRef);
                } catch (AnnotationException $e) {
                    // todo we have lots of @option that throw AnnotationException. ignore or cleanup?
                }
            }

            foreach ($reflection->getMethods() as $methodRef) {
                try {
                    $annotaionReader->getMethodAnnotations($methodRef);
                } catch (AnnotationException $e) {
                    // todo we have lots of @option that throw AnnotationException. ignore or cleanup?
                }
            }
        }
    }
}
