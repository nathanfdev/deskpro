<?php

namespace DpTest\DeskPRO\Application\JobQueue;

use Application\DeskPRO\Entity\Job;
use Application\DeskPRO\Entity\Setting;
use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\JobQueue\Processor\FeatureProcessor;
use DeskPRO\Bundle\AppBundle\Features\BetaFeatureInterface;
use DpTest\ApiTestCase;
use DpTestSrc\TestBundle\Mock\Features\DisabledFeature;
use DpTestSrc\TestBundle\Mock\Features\EnabledFeature;

class FeatureProcessorTest extends ApiTestCase
{
    /**
     * @var FeatureProcessor
     */
    private $featureProcessor;

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function setUp()
    {
        $em = $this->getEntityManager();
        $em->getConnection()->executeQuery('DELETE FROM jobs');

        $this
            ->getContainer()
            ->get('deskpro.features_collection')
            ->addFeature(new EnabledFeature())
            ->addFeature(new DisabledFeature())
        ;

        $this->featureProcessor = new FeatureProcessor(
            $em->getConnection(),
            $this->getContainer()->get('deskpro.toggle_feature_manager')
        );
    }

    public function test_resolver_options()
    {
        $jobQueue = $this->get('job.queue');
        $job      = $jobQueue->add(FeatureProcessor::JOB_TYPE, []);

        $this->featureProcessor->execute($this->getJobArray($job));
        $this->getEntityManager()->refresh($job);

        $this->assertEquals('rejected', $job->status);
        $this->assertEquals('invalid_data', $job->status_code);
        $this->assertEquals('invalid job data', $job->log_summary);
    }

    public function test_process_disabling_feature()
    {
        $jobQueue = $this->get('job.queue');
        $job      = $jobQueue->add(FeatureProcessor::JOB_TYPE, [
                'feature_id' => 'enabled_feature',
                'action'     => 'disable', ]
        );
        $this->featureProcessor->execute($this->getJobArray($job));

        $this->getEntityManager()->refresh($job);

        $this->assertEquals('complete', $job->status);
        $this->assertEquals('success', $job->status_code);
        $this->assertEquals('Successful', $job->log_summary);
    }

    public function test_process_disabling_disabled_feature()
    {
        $jobQueue = $this->get('job.queue');
        $job      = $jobQueue->add(FeatureProcessor::JOB_TYPE, [
                'feature_id' => 'disabled_feature',
                'action'     => 'disable', ]
        );
        $this->featureProcessor->execute($this->getJobArray($job));
        $this->getEntityManager()->refresh($job);

        $this->assertEquals('complete', $job->status);
        $this->assertEquals('success', $job->status_code);
        $this->assertEquals('Successful', $job->log_summary);
    }

    public function test_process_enabling_feature()
    {
        $jobQueue = $this->get('job.queue');
        $job      = $jobQueue->add(FeatureProcessor::JOB_TYPE, [
                'feature_id' => 'disabled_feature',
                'action'     => 'enable', ]
        );
        $this->featureProcessor->execute($this->getJobArray($job));

        $this->getEntityManager()->refresh($job);

        $this->assertEquals('complete', $job->status);
        $this->assertEquals('success', $job->status_code);
        $this->assertEquals('Successful', $job->log_summary);
    }

    public function test_process_enabling_enabled_feature()
    {
        $jobQueue = $this->get('job.queue');
        $job      = $jobQueue->add(FeatureProcessor::JOB_TYPE, [
                'feature_id' => 'enabled_feature',
                'action'     => 'enable', ]
        );
        $this->featureProcessor->execute($this->getJobArray($job));
        $this->getEntityManager()->refresh($job);

        $this->assertEquals('complete', $job->status);
        $this->assertEquals('success', $job->status_code);
        $this->assertEquals('Successful', $job->log_summary);
    }

    public function test_enable()
    {
        $jobQueue = $this->get('job.queue');
        $job      = $jobQueue->add(FeatureProcessor::JOB_TYPE, [
            'feature_id' => 'disabled_feature',
            'action'     => 'enable',
        ]);

        $this->setUpSetting('disabled_feature', false);
        $this->setUpTmpData('disabled_feature', false);

        $this->featureProcessor->execute($this->getJobArray($job));
        $this->getEntityManager()->refresh($job);

        $key     = $this->getKey('disabled_feature');
        $em      = $this->getEntityManager();
        $setting = $em->getRepository(Setting::class)->findOneBy(['name' => $key]);
        $tmpData = $em->getRepository(TmpData::class)->findOneBy(['name' => $key], ['date_expire' => 'DESC']);

        $this->assertTrue((bool) $setting->getValue());
        $this->assertNull($tmpData); // tmpData is deleted during enable
    }

    public function test_disable()
    {
        $jobQueue = $this->get('job.queue');
        $job      = $jobQueue->add(FeatureProcessor::JOB_TYPE, [
            'feature_id' => 'enabled_feature',
            'action'     => 'disable',
        ]);

        $this->setUpSetting('enabled_feature', true);
        $this->setUpTmpData('enabled_feature', true);

        $this->featureProcessor->execute($this->getJobArray($job));
        $this->getEntityManager()->refresh($job);

        /** @var Setting $setting */
        $key     = $this->getKey('enabled_feature');
        $em      = $this->getEntityManager();
        $setting = $em->getRepository(Setting::class)->findOneBy(['name' => $key]);
        $tmpData = $em->getRepository(TmpData::class)->findOneBy(['name' => $key], ['date_expire' => 'DESC']);

        $this->assertFalse((bool) $setting->getValue());
        $this->assertNull($tmpData); // tmpData is deleted during disable
    }

    /**
     * @param Job $job
     *
     * @return array
     */
    private function getJobArray(Job $job)
    {
        $jobArray         = $job->toArray();
        $jobArray['data'] = json_encode($jobArray['data']);

        return $jobArray;
    }

    /**
     * @param string $id
     * @param bool   $enabled
     */
    private function setUpSetting($id, $enabled)
    {
        $key = $this->getKey($id);
        $em  = $this->getEntityManager();

        $settingsRepo = $em->getRepository(Setting::class);
        $settingsRepo->updateSetting($key, $enabled);
    }

    /**
     * @param string $id
     * @param bool   $enabled
     */
    private function setUpTmpData($id, $enabled)
    {
        $key = $this->getKey($id);
        $em  = $this->getEntityManager();

        $type = sprintf('feature_%s', $enabled ? 'disable' : 'enable');

        $tmpData = TmpData::create($type, [], '+20 min', $key);
        $em->persist($tmpData);
        $em->flush();
    }

    /**
     * @param string $id
     *
     * @return string
     */
    private function getKey($id)
    {
        return sprintf('%s.%s', BetaFeatureInterface::BETA_FEATURES_KEY, $id);
    }
}
