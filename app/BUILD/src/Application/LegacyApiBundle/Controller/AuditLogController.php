<?php

/**
 * DeskPRO.
 */
namespace Application\LegacyApiBundle\Controller;

use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use DeskPRO\Kernel\KernelErrorHandler;
use Orb\Util\Arrays;
use Orb\Util\Numbers;

class AuditLogController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        return new AdminManagePermission();
    }

    ####################################################################################################################
    # list
    ####################################################################################################################

    public function listAction()
    {
        $per_page  = 50;
        $total     = $this->db->count('auditlog');
        $num_pages = $total ? ceil($total / $per_page) : 1;
        $page      = Numbers::bound($this->in->getUint('page'), 1, $num_pages);

        $limit_start = ($page - 1) * $per_page;

        $recs = $this->db->fetchAll("
            SELECT *
            FROM auditlog
            ORDER BY id DESC
            LIMIT $limit_start, $per_page
        ");

        $people_ids = array();
        foreach ($recs as $r) {
            $people_ids[] = $r['person_id'] ?: null;
        }
        $people_ids = Arrays::removeFalsey($people_ids);

        $people = $this->em->getRepository('DeskPRO:Person')->getByIds($people_ids);

        $rec_data = array();
        foreach ($recs as $rec) {
            $person = $rec['person_id'] && isset($people[$rec['person_id']]) ? $people[$rec['person_id']] : null;

            $new_val = null;
            if ($rec['op'] == 'update') {
                $data    = unserialize($rec['data']);
                $new_val = array();
                foreach ($data as $r) {
                    $v = $r['new_val'];
                    if ($v === true) {
                        $v = 'true';
                    }
                    if ($v === false) {
                        $v = 'false';
                    }
                    if ($v === null) {
                        $v = 'null';
                    }
                    if (is_array($v)) {
                        if (empty($v)) {
                            $v = '[]';
                        } else {
                            $v = '['.implode(',', $v).']';
                        }
                    }
                    $new_val[$r['field_id']] = $v;
                }
            }

            $row = $rec;
            unset($row['data']);
            $row['change_data'] = $new_val;
            $row['person']      = $person ? $person->toApiData(true, false) : null;

            $d                         = \DateTime::createFromFormat('Y-m-d H:i:s', $rec['date_created']);
            $row['date_created_ts']    = $d->getTimestamp();
            $row['date_created_ts_ms'] = $d->getTimestamp() * 1000;

            $rec_data[] = $row;
        }

        return $this->createApiResponse(array(
            'logs'      => $rec_data,
            'total'     => $total,
            'per_page'  => $per_page,
            'page'      => $page,
            'num_pages' => $num_pages,
        ));
    }

    ####################################################################################################################
    # detail
    ####################################################################################################################

    public function getDetailAction($id)
    {
        $log = $this->em->find('DeskPRO:AuditLog', $id);
        if (!$log) {
            throw $this->createNotFoundException();
        }

        $data = $log->toApiData();
        unset($data['data']);
        $data['raw_data'] = KernelErrorHandler::varToString($log->data);

        return $this->createApiResponse(array('log' => $data));
    }
}
