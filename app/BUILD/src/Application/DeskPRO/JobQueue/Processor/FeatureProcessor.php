<?php

namespace Application\DeskPRO\JobQueue\Processor;

use DeskPRO\Bundle\AppBundle\Features\ToggleFeatureManager;
use Doctrine\DBAL\Connection;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FeatureProcessor.
 */
class FeatureProcessor extends AbstractJobProcessor
{
    const JOB_TYPE = 'feature_switch';

    /**
     * @var ToggleFeatureManager
     */
    private $toggleFeatureManager;

    /**
     * Constructor.
     *
     * @param Connection           $connection
     * @param ToggleFeatureManager $toggleFeatureManager
     */
    public function __construct(Connection $connection, ToggleFeatureManager $toggleFeatureManager)
    {
        parent::__construct($connection);
        $this->toggleFeatureManager = $toggleFeatureManager;
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
        try {
            if ($data['action'] === 'enable') {
                $this->toggleFeatureManager->enableFeature($data['feature_id']);
            } else {
                $this->toggleFeatureManager->disableFeature($data['feature_id']);
            }

            $this->runSuccessHandler($job);
        } catch (\Exception $e) {
            $this->runExceptionHandler($job, $e);
        }
    }
}
