<?php

namespace DeskPRO\Bundle\AppBundle\Features;

use Application\DeskPRO\Entity\Setting;
use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\EntityRepository\Setting as SettingRepository;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class ToggleFeatureManager.
 */
class ToggleFeatureManager
{
    /**
     * @var FeaturesCollection
     */
    private $featuresCollection;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor.
     *
     * @param FeaturesCollection       $featuresCollection
     * @param EntityManager            $em
     * @param EventDispatcherInterface $dispatcher
     * @param ContainerInterface       $container
     */
    public function __construct(
        FeaturesCollection       $featuresCollection,
        EntityManager            $em,
        EventDispatcherInterface $dispatcher,
        ContainerInterface       $container
    ) {
        $this->featuresCollection = $featuresCollection;
        $this->em                 = $em;
        $this->dispatcher         = $dispatcher;
        $this->container          = $container;
    }

    /**
     * @param string $id
     */
    public function disableFeature($id)
    {
        $feature = $this->getFeature($id);
        if (!$feature->isEnabled()) {
            return;
        }

        $feature->beforeDisable($this->container);
        $this->toggleFeature($feature, false);
    }

    /**
     * @param string $id
     */
    public function enableFeature($id)
    {
        $feature = $this->getFeature($id);
        if ($feature->isEnabled()) {
            return;
        }

        $feature->beforeEnable($this->container);
        $this->toggleFeature($feature, true);
    }

    /**
     * @param string $id
     *
     * @return BetaFeatureInterface
     */
    private function getFeature($id)
    {
        $feature = $this->featuresCollection->getFeature($id);
        if (!$feature) {
            throw new \RuntimeException("Feature '$id' not found!");
        }

        return $feature;
    }

    /**
     * @param BetaFeatureInterface $feature
     * @param bool                 $enabled
     */
    private function toggleFeature(BetaFeatureInterface $feature, $enabled)
    {
        $key = sprintf('%s.%s', BetaFeatureInterface::BETA_FEATURES_KEY, $feature->getId());

        // enable or disable feature in global settings
        /** @var SettingRepository $settingsRepository */
        $settingsRepository = $this->em->getRepository(Setting::class);
        $settingsRepository->updateSetting($key, $enabled);

        // remove status indicators
        $this
            ->em
            ->getRepository(TmpData::class)
            ->createQueryBuilder('t')
            ->delete()
            ->where('t.name = :name')
            ->setParameter('name', $key)
            ->getQuery()
            ->execute()
        ;

        // broadcast a refresh event to all agents
        if ($feature->needAgentReload()) {
            $this->dispatcher->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent('agent.ui.reload', [
                'type'        => 'admin',
                'person_id'   => 0,
                'person_name' => 'System',
            ]));
        }
    }
}
