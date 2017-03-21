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

namespace DpTest\DeskPRO\Application\JobQueue;

use Application\DeskPRO\Entity\Job;
use Application\DeskPRO\JobQueue\JobQueue;
use Application\DeskPRO\JobQueue\Processor\FeatureProcessor;
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

        $this->getContainer()->get('deskpro.features_collection')
            ->addFeature(new EnabledFeature())
            ->addFeature(new DisabledFeature());

        $this->featureProcessor = new FeatureProcessor(
            $em->getConnection(),
            $this->getContainer()
        );
    }

    public function test_resolver_options()
    {
        /** @var JobQueue $jobQueue */
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
        /** @var JobQueue $jobQueue */
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
        /** @var JobQueue $jobQueue */
        $jobQueue = $this->get('job.queue');
        $job      = $jobQueue->add(FeatureProcessor::JOB_TYPE, [
                'feature_id' => 'disabled_feature',
                'action'     => 'disable', ]
        );
        $this->featureProcessor->execute($this->getJobArray($job));
        $this->getEntityManager()->refresh($job);

        $this->assertEquals('error', $job->status);
        $this->assertEquals('failed', $job->status_code);
        $this->assertTrue(strpos($job->log, 'Code: 400') !== false);
    }

    public function test_process_enabling_feature()
    {
        /** @var JobQueue $jobQueue */
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
        /** @var JobQueue $jobQueue */
        $jobQueue = $this->get('job.queue');
        $job      = $jobQueue->add(FeatureProcessor::JOB_TYPE, [
                'feature_id' => 'enabled_feature',
                'action'     => 'enable', ]
        );
        $this->featureProcessor->execute($this->getJobArray($job));
        $this->getEntityManager()->refresh($job);

        $this->assertEquals('error', $job->status);
        $this->assertEquals('failed', $job->status_code);
        $this->assertTrue(strpos($job->log, 'Code: 400') !== false);
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
}
