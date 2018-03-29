<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\App\Package;

use DeskPRO\Component\Filesystem\SafeFile;
use Orb\Util\Arrays;
use Orb\Util\Strings;
use Orb\Validator\StringEmail;

class ManifestReader
{
    const ERR_INVALID_FILE = 'invalid_file';
    const ERR_BAD_FORMAT   = 'bad_format';

    /**
     * @var array
     */
    private $data;

    /**
     * @var Manifest
     */
    private $manifest;

    /**
     * @var string
     */
    private $error_code = null;

    /**
     * @var array
     */
    private $error_details = [];

    /**
     * @param array $data
     *
     * @return ManifestReader
     */
    public static function newFromArray(array $data)
    {
        return new self($data);
    }

    /**
     * @param string $path
     *
     * @return ManifestReader
     */
    public static function newFromFile($path)
    {
        if (!is_file($path)) {
            return new self([], self::ERR_INVALID_FILE, ['file', 'missing_path']);
        }

        $json = SafeFile::fileGetContents($path, dirname($path));

        return self::newFromJson($json);
    }

    /**
     * @param string $json
     *
     * @return ManifestReader
     */
    public static function newFromJson($json)
    {
        $data = @json_decode($json, true);
        if (!$data) {
            return new self([], self::ERR_INVALID_FILE, ['file', 'invalid_json']);
        }

        return new self($data);
    }

    /**
     * @param array $data
     * @param null  $set_error
     * @param array $set_error_detail
     */
    private function __construct(array $data, $set_error = null, array $set_error_detail = null)
    {
        $this->data     = $data;
        $this->manifest = new Manifest();

        if ($set_error) {
            $this->error_code = $set_error;
            if ($set_error_detail) {
                $this->error_details = [$set_error_detail];
            }
        } else {
            $fields = [
                'package_name',
                'is_native',
                'title',
                'description',
                'api_version',
                'version',
                'version_name',
                'is_single',
                'author.name',
                'author.email',
                'author.link',
                'tags',
                'trigger_events',
                'settings_def',
            ];

            $docheck = [];

            foreach ($fields as $f) {
                $setter = Strings::underscoreToCamelCase('set_'.str_replace('.', '_', $f));
                $value  = Arrays::getValue($this->data, $f, '___dp_unset___');
                if ($value === '___dp_unset___') {
                    if ($f == 'tags' || $f == 'is_native' || $f == 'trigger_events') {
                        // allowed to be unset
                        continue;
                    }
                    $this->error_details[] = ['missing', $f];
                } elseif ($f == 'settings_def') {
                    if (!is_array($value)) {
                        $this->error_details[] = ['invalid', $f];
                    } else {
                        $this->manifest->$setter($value);
                    }
                } elseif ($f == 'trigger_events') {
                    if (!is_array($value)) {
                        $this->error_details[] = ['invalid', $f];
                    } else {
                        $this->manifest->$setter($value);
                    }
                } elseif ($f == 'tags') {
                    if (!is_array($value)) {
                        $this->error_details[] = ['invalid', $f];
                    } else {
                        $this->manifest->$setter($value);
                    }
                } elseif ($f == 'api_version') {
                    $value = (int) $value;
                    if ($value != 1) {
                        $this->error_details[] = ['invalid', $f];
                    } else {
                        $this->manifest->$setter($value);
                    }
                } elseif ($f == 'is_native') {
                    $value = (bool) $value;
                    $this->manifest->setIsNative($value);
                } else {
                    if (!is_scalar($value)) {
                        $this->error_details[] = ['invalid', $f];
                    } else {
                        $value = trim($value);
                        $this->manifest->$setter($value);
                        $docheck[] = $f;
                    }
                }
            }

            if ($docheck) {
                $this->validate($docheck);
            }

            if ($this->error_details) {
                $this->error_code = self::ERR_BAD_FORMAT;
            }
        }
    }

    /**
     * @param array $fields
     */
    private function validate(array $fields)
    {
        foreach ($fields as $f) {
            $getter = Strings::underscoreToCamelCase('get_'.str_replace('.', '_', $f));
            $value  = $this->manifest->$getter();

            switch ($f) {
                case 'api_version':
                case 'version':
                    if (!$value) {
                        $this->error_details[] = ['invalid', $f];
                    }
                    break;

                case 'author.email':
                    if (!StringEmail::isValueValid($value)) {
                        $this->error_details[] = ['invalid', $f];
                    }
                    break;

                case 'author.link':
                    if (!preg_match('#^https?://#', $value)) {
                        $this->error_details[] = ['invalid', $f];
                    }
                    break;
            }
        }
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return $this->error_code !== null;
    }

    /**
     * @return string
     */
    public function getErrorCode()
    {
        return $this->error_code;
    }

    /**
     * @return array
     */
    public function getErrorDetail()
    {
        return $this->error_details;
    }

    /**
     * @return string
     */
    public function getErrorDetailAsString()
    {
        $lines = [];
        foreach ($this->error_details as $err) {
            $lines[] = sprintf('[%s] %s', $err[0], $err[1]);
        }

        return implode("\n", $lines);
    }

    /**
     * @return Manifest
     */
    public function getManifest()
    {
        return $this->manifest;
    }
}
