<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Form\Form\DataTransformer;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\EntityRepository\Blob as BlobRepo;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * BlobType has an array as its "normalized" data:.
 *
 * - blob (Blob entity)
 * - blob_auth (the persisted blob's authcode)
 * - upload (the uploaded file)
 * - delete_blob (bool)
 */
class BlobTypeViewTransformer implements DataTransformerInterface
{
    public static $default_normailzed = [
        'blob'        => null,
        'blob_auth'   => null,
        'upload'      => null,
        'delete_blob' => false,
    ];

    /**
     * @var \Application\DeskPRO\EntityRepository\Blob
     */
    private $repo;

    public function __construct(BlobRepo $repo)
    {
        $this->repo = $repo;
    }

    public function transform($value)
    {
        return isset($default_normailzed['blob']) ? $default_normailzed['blob'] : null;
    }

    public function reverseTransform($value)
    {
        $data = array_merge(self::$default_normailzed, $value);

        if ($data['blob'] instanceof Blob) {
            return $data['blob'];
        }

        if ($data['blob_auth']) {
            return $this->repo->getByAuthId($data['blob_auth']);
        }
    }
}
