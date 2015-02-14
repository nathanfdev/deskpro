<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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

namespace Application\ImportBundle\Generator\Writer\DeskPro\Importer\Mapper;

/**
 * Blob data mapper
 *
 * Class BlobData
 * @package Application\ImportBundle\Generator\Writer\DeskPro\Importer\Mapper
 */
final class BlobData implements MapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_BLOB_DATA;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria, $throw_exception = true)
    {
        $data = null;
        if ( ! empty($criteria['data'])) {
            $data = base64_decode($criteria['data']);
        }
        if ( ! empty($criteria['path'])) {
            if (!is_readable($criteria['path'])) {
                throw new MapperException(sprintf('Invalid blob path %s', $criteria['path']), $criteria);
            }

            $data = @file_get_contents($criteria['path']);
        }
        if ( ! empty($criteria['url'])) {
            $data = @file_get_contents($criteria['url']);
        }
        if ( ! $data) {
            throw new MapperException('Blob data not found', $criteria);
        }

        return $data;
    }

    /**
     * Returns blob data by params
     *
     * @param string $data
     * @param string $path
     * @param string $url
     * @param bool   $throw_exception
     *
     * @return mixed|null|string
     * @throws MapperException
     */
    public function findOneByParams($data, $path, $url, $throw_exception = true)
    {
        return $this->findOneBy(
            array(
                'data' => $data,
                'path' => $path,
                'url'  => $url,
            ),
            $throw_exception
        );
    }
}
