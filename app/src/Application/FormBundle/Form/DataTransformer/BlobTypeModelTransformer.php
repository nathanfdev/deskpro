<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 * @subpackage
 */

namespace Application\FormBundle\Form\DataTransformer;


use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\EntityRepository\Blob as BlobRepo;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * BlobType has an array as its "normalized" data:
 *
 * - blob (Blob entity)
 * - blob_auth (the persisted blob's authcode)
 * - upload (the uploaded file)
 * - delete_blob (bool)
 */
class BlobTypeModelTransformer implements DataTransformerInterface
{
    public static $default_normailzed = array(
        'blob'        => null,
        'blob_auth'   => null,
        'upload'      => null,
        'delete_blob' => false
    );

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
        $default_normailzed = self::$default_normailzed;

        if ($value instanceof Blob) {
            $default_normailzed['blob'] = $value;
            $default_normailzed['blob_auth'] = $value->getAuthId();
        }

        return $default_normailzed;
    }


    public function reverseTransform($value)
    {
        $data = array_merge(self::$default_normailzed, $value);

        if ($data['blob'] instanceof Blob) {
            return $data['blob'];
        }

        if ($data['blob_auth']) {
            return $this->repo->getByAuthId($data['blob_auth']) ?: new Blob();
        }

        return new Blob();
    }
}
 