<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use DeskPRO\Component\Filesystem\SafeFile;

/**
 * Blob data mapper.
 *
 * Class BlobData
 */
class BlobDataMapper
{
    /**
     * {@inheritdoc}
     */
    public static function findOneBy(array $criteria)
    {
        /* @var $DP_ENV \DpRun\DpEnv */
        global $DP_ENV;

        $data = null;
        if (!empty($criteria['data'])) {
            $data = base64_decode($criteria['data']);
        }
        if (!empty($criteria['path'])) {
            if (SafeFile::isValid($criteria['path'], [
                $DP_ENV->getUserTmpDir(),
                $DP_ENV->getUserCacheDir(),
                sys_get_temp_dir(),
            ])) {
                $data = @file_get_contents($criteria['path']);
            }
        }
        if (!empty($criteria['url']) && preg_match('/^https?:\/\//', $criteria['url'])) {
            $data = @file_get_contents($criteria['url']);
        }

        return $data;
    }

    /**
     * Returns blob data by params.
     *
     * @param string $data
     * @param string $path
     * @param string $url
     *
     * @return mixed|null|string
     */
    public static function findOneByParams($data, $path, $url)
    {
        $criteria = [
            'data' => $data,
            'path' => $path,
            'url'  => $url,
        ];

        return self::findOneBy($criteria);
    }
}
