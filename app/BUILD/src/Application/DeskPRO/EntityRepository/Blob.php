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

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;

class Blob extends AbstractEntityRepository
{
    /**
     * Get a blob by a combined ID/authcode.
     *
     * @returb \Application\DeskPRO\Entity\Blob
     */
    public function getByAuthId($auth_id)
    {
        if (strpos($auth_id, '-') === false) {
            return $this->getByAuthCode($auth_id);
        }

        list($blob_id, $authcode) = explode('-', $auth_id, 2);
        $blob                     = App::findEntity('DeskPRO:Blob', $blob_id);
        if ($blob && $blob->getAuthId() != $authcode) {
            $blob = null;
        }

        return $blob;
    }

    /**
     * @param string $auth_code
     *
     * @return \Application\DeskPRO\Entity\Blob|null
     */
    public function getByAuthCode($auth_code)
    {
        return $this->getEntityManager()->createQuery('
            SELECT b
            FROM DeskPRO:Blob b
            WHERE b.authcode = ?0
        ')->setParameters([$auth_code])->getOneOrNullResult();
    }

    public function getByAuthCodes($auth_codes)
    {
        $auth_codes = (array) $auth_codes;
        if (!$auth_codes) {
            return [];
        }

        return $this->getEntityManager()->createQuery('
            SELECT b
            FROM DeskPRO:Blob b INDEX BY b.id
            WHERE b.authcode IN(?0)
        ')->execute([$auth_codes]);
    }

    public function getSystemBlob($sys_name)
    {
        return $this->findOneBy(['sys_name' => $sys_name]);
    }

    /**
     * Counts rows in blobs_storage that have no parent blob existing.
     * Note: intensive.
     *
     * @return int
     */
    public function countDanglingBlobStorageRows()
    {
        return $this->_em->getConnection()->fetchColumn('
            SELECT COUNT(*)
            FROM blobs_storage
            LEFT JOIN blobs ON blobs.id = blobs_storage.blob_id
            WHERE blobs.id IS NULL
        ');
    }

    /**
     * DELETE's rows in blob_storage that have no parent blob record.
     * Note: intensive.
     *
     * @return int
     */
    public function cleanDanglingBlobStorageRows()
    {
        return $this->_em->getConnection()->executeUpdate('
            DELETE blobs_storage
            FROM blobs_storage
            LEFT JOIN blobs ON blobs.id = blobs_storage.blob_id
            WHERE blobs.id IS NULL
        ');
    }
}
