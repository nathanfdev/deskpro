<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace Application\DeskPRO\JobQueue\Processor;

use Application\DeskPRO\Entity\Setting;
use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\EntityRepository\Setting as SettingRepository;
use DeskPRO\Bundle\AppBundle\Features\BetaFeatureInterface;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FeatureProcessor.
 */
class FeatureProcessor extends AbstractJobProcessor
{
    const JOB_TYPE = 'feature_switch';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor.
     *
     * @param Connection         $connection
     * @param ContainerInterface $container
     */
    public function __construct(
        Connection $connection,
        ContainerInterface $container
    ) {
        parent::__construct($connection);
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('feature_id');
        $resolver->setRequired('action');
        $resolver->setAllowedValues('action', ['enable', 'disable']);
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $data, array $job)
    {
        $featureId = $data['feature_id'];
        $action    = $data['action'];

        $collection = $this->container->get('deskpro.features_collection');
        $feature    = $collection->getFeature($featureId);

        if (!$feature) {
            throw new \LogicException(sprintf('Feature with id %s not found in collection!'), $featureId);
        }

        try {
            if ($action === 'enable') {
                if ($feature->isEnabled()) {
                    throw new \LogicException(sprintf('Feature %s already enabled', $feature->getTitle()), 400);
                }
                $feature->beforeEnable($this->container);
            } elseif ($action === 'disable') {
                if (!$feature->isEnabled()) {
                    throw new \LogicException(sprintf('Feature %s already disabled', $feature->getTitle()), 400);
                }
                $feature->beforeDisable($this->container);
            }

            $em  = $this->container->get('doctrine.orm.default_entity_manager');
            $key = sprintf('%s.%s', BetaFeatureInterface::BETA_FEATURES_KEY, $feature->getId());

            // enable or disable feature in global settings
            /** @var SettingRepository $settingsRepository */
            $settingsRepository = $em->getRepository(Setting::class);
            $settingsRepository->updateSetting($key, $action === 'enable');

            // remove status indicators
            $tmpData = $em->getRepository(TmpData::class)->findBy(['name' => $key]);
            foreach ($tmpData as $tmpDatum) {
                if ($tmpDatum->getType() === 'feature_'.$action) {
                    $em->remove($tmpDatum);
                }
            }

            $em->flush();

            // broadcast a refresh event to all agents
            if ($feature->needAgentReload()) {
                $this->container
                    ->get('event_dispatcher')
                    ->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent('agent.ui.reload', [
                        'type'        => 'admin',
                        'person_id'   => 0,
                        'person_name' => 'System',
                    ]));
            }

            $this->runSuccessHandler($job);
        } catch (\Exception $e) {
            $this->runExceptionHandler($job, $e);
        }
    }
}
