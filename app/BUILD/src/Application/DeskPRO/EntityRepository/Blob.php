<?php

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
