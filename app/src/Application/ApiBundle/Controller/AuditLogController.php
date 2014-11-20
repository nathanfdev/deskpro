<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;

use Application\ApiBundle\PermissionStrategy\AdminManagePermission;
use DeskPRO\Kernel\KernelErrorHandler;
use Orb\Util\Arrays;
use Orb\Util\Numbers;

class AuditLogController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritDoc}
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

        $limit_start = ($page-1)*$per_page;

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
