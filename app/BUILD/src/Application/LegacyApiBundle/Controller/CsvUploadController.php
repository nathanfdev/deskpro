<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace Application\LegacyApiBundle\Controller;

use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\DeskPRO\Entity\Job;
use Application\DeskPRO\HttpFoundation\Request;
use Application\DeskPRO\JobQueue\Processor\Reset\UsersImportProcessor;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CsvUploadController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        return new AdminManagePermission();
    }

    ####################################################################################################################
    # upload
    ####################################################################################################################

    public function uploadAction()
    {
        $file = $this->request->files->get('file');

        /*
         * @var \Application\DeskPRO\CsvUpload\CsvUpload
         */
        $csv_upload = $this->container->getSystemService('csv_upload');
        $options    = $this->in->getArrayValue('options');

        $result                  = $csv_upload->upload($file, $options);
        $result['custom_fields'] = $this->getApiData($result['custom_fields']);

        return $this->createApiResponse($result);
    }

    ####################################################################################################################
    # import
    ####################################################################################################################

    public function importAction()
    {
        $field_maps       = $this->in->getCleanValueArray('field_maps', 'raw', 'uint');
        $user_filename    = $this->in->getString('user_filename');
        $skip_first       = $this->in->getBool('skip_first');
        $update_if_exists = $this->in->getBool('update_if_exists');
        $welcome_email    = $this->in->getBool('welcome_email');
        $filename         = $this->in->getUint('filename');
        $options          = $this->in->getArrayValue('options');

        /*
         * @var \Application\DeskPRO\CsvUpload\CsvUpload
         */
        $csv_upload = $this->container->getSystemService('csv_upload');

        $result = $csv_upload->startImportTask($field_maps, $filename, $user_filename, $skip_first, $welcome_email, $update_if_exists, $options);

        return $this->createApiResponse($result);
    }

    ####################################################################################################################
    # status
    ####################################################################################################################

    public function statusAction()
    {
        /*
         * @var \Application\DeskPRO\CsvUpload\CsvUpload
         */
        $csv_upload = $this->container->getSystemService('csv_upload');

        $result = $csv_upload->returnStatusOfImport();

        return $this->createApiResponse($result);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function logsAction()
    {
        $logs      = array();
        $deletions = $this->getDeletionStatus();
        foreach ($this->em->getRepository('DeskPRO:DataStore')->getByPrefix('csv_import.') as $entity) {
            $data                    = $entity->toApiData();
            $data['deletion_status'] = @$deletions[str_replace('csv_import.', '', $data['name'])];
            $logs[]                  = $data;
        }

        return $this->createApiResponse($logs);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cleanAction(Request $request)
    {
        if (!$ref = $request->get('ref')) {
            throw new NotFoundHttpException();
        }

        $statuses = $this->getDeletionStatus();
        if ('waiting' === @$statuses[$ref]) {
            return $this->createApiResponse(array());
        }

        $queue = $this->container->getJobQueue();
        $queue->add(
            UsersImportProcessor::JOB_TYPE,
            array(
            'context_person_id' => $this->person['id'],
            'labeled_by'        => 'import-'.$ref,
        ));
        $this->em->flush();

        return $this->createApiResponse(array());
    }

    /**
     * get statuses of users deletion jobs.
     *
     * @return array
     */
    protected function getDeletionStatus()
    {
        $res = array();
        $rep = $this->em->getRepository('DeskPRO:Job');

        foreach ($rep->findBy(
            array('type' => UsersImportProcessor::JOB_TYPE),
            array('date_created' => 'desc'),
            1
        ) as $job) {
            /* @var $job Job */
            $data = $job['data'];
            if (!$ref = @$data['labeled_by']) {
                continue;
            }
            if (isset($res[$ref])) {
                continue;
            }

            $ref    = str_replace('import-', '', $ref);
            $status = $job['status'];
            if (in_array($status, array('rejected', 'aborted'))) {
                $status = 'error';
            }
            if (in_array($status, array('inserting', 'reserved', 'processing'))) {
                $status = 'waiting';
            }

            $res[$ref] = $status;
        }

        return $res;
    }
}
