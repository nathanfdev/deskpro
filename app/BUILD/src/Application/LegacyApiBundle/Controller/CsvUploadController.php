<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\CsvUpload\CsvUpload;
use Application\DeskPRO\Entity\Job;
use Application\DeskPRO\JobQueue\Processor\Reset\UsersImportProcessor;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @ApiModes("all")
 */
class CsvUploadController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        return new AdminManagePermission();
    }

    //###################################################################################################################
    // upload
    //###################################################################################################################

    public function uploadAction()
    {
        $file = $this->request->files->get('file');

        /*
         * @var \Application\DeskPRO\CsvUpload\CsvUpload
         */
        $csv_upload = $this->container->getSystemService('csv_upload');
        $options    = $this->in->getArrayValue('options');

        $result = $csv_upload->upload($file, $options);
        if (isset($result['custom_fields'])) {
            $result['custom_fields'] = $this->getApiData($result['custom_fields']);
        }

        return $this->createApiResponse($result);
    }

    //###################################################################################################################
    // import
    //###################################################################################################################

    public function importAction()
    {
        $field_maps       = $this->in->getCleanValueArray('field_maps', 'raw', 'uint');
        $user_filename    = $this->in->getString('user_filename');
        $skip_first       = $this->in->getBool('skip_first');
        $update_if_exists = $this->in->getBool('update_if_exists');
        $welcome_email    = $this->in->getBool('welcome_email');
        $filename         = $this->in->getUint('filename');
        $options          = $this->in->getArrayValue('options');

        /** @var CsvUpload $csv_upload */
        $csv_upload = $this->container->getSystemService('csv_upload');

        $result = $csv_upload->startImportTask($field_maps, $filename, $user_filename, $skip_first, $welcome_email, $update_if_exists, $options);

        return $this->createApiResponse($result);
    }

    //###################################################################################################################
    // status
    //###################################################################################################################

    public function statusAction()
    {
        /** @var CsvUpload $csv_upload */
        $csv_upload = $this->container->getSystemService('csv_upload');

        $result = $csv_upload->returnStatusOfImport();

        return $this->createApiResponse($result);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function logsAction()
    {
        $logs      = [];
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
            return $this->createApiResponse([]);
        }

        $queue = $this->container->getJobQueue();
        $queue->add(
            UsersImportProcessor::JOB_TYPE,
            [
            'context_person_id' => $this->person['id'],
            'labeled_by'        => 'import-'.$ref,
        ]);
        $this->em->flush();

        return $this->createApiResponse([]);
    }

    /**
     * get statuses of users deletion jobs.
     *
     * @return array
     */
    protected function getDeletionStatus()
    {
        $res = [];
        $rep = $this->em->getRepository('DeskPRO:Job');

        foreach ($rep->findBy(
            ['type' => UsersImportProcessor::JOB_TYPE],
            ['date_created' => 'desc'],
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
            if (in_array($status, ['rejected', 'aborted'])) {
                $status = 'error';
            }
            if (in_array($status, ['inserting', 'reserved', 'processing'])) {
                $status = 'waiting';
            }

            $res[$ref] = $status;
        }

        return $res;
    }

    public function cancelImportAction(Request $request)
    {
        /** @var CsvUpload $csvUpload */
        $csvUpload = $this->container->getSystemService('csv_upload');
        $csvUpload->cancelImport();

        return $this->createSuccessResponse();
    }
}
