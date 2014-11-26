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
 * @category Entities
 */

namespace Application\DeskPRO\App\Package;

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
    private $error_details = array();


    /**
     * @param  array          $data
     * @return ManifestReader
     */
    public static function newFromArray(array $data)
    {
        return new self($data);
    }


    /**
     * @param  string         $path
     * @return ManifestReader
     */
    public static function newFromFile($path)
    {
        if (!is_file($path)) {
            return new self(array(), self::ERR_INVALID_FILE, array('file', 'missing_path'));
        }

        $json = @file_get_contents($path);

        return self::newFromJson($json);
    }


    /**
     * @param  string         $json
     * @return ManifestReader
     */
    public static function newFromJson($json)
    {
        $data = @json_decode($json, true);
        if (!$data) {
            return new self(array(), self::ERR_INVALID_FILE, array('file', 'invalid_json'));
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
        $this->data = $data;
        $this->manifest = new Manifest();

        if ($set_error) {
            $this->error_code = $set_error;
            if ($set_error_detail) {
                $this->error_details = array($set_error_detail);
            }
        } else {
            $fields = array(
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
            );

            $docheck = array();

            foreach ($fields as $f) {
                $setter = Strings::underscoreToCamelCase('set_' . str_replace('.', '_', $f));
                $value = Arrays::getValue($this->data, $f, '___dp_unset___');
                if ($value === '___dp_unset___') {
					if ($f == 'tags' || $f == 'is_native' || $f == 'trigger_events') {
                        // allowed to be unset
                        continue;
                    }
                    $this->error_details[] = array('missing', $f);
                } elseif ($f == 'settings_def') {
                    if (!is_array($value)) {
                        $this->error_details[] = array('invalid', $f);
                    } else {
                        $this->manifest->$setter($value);
                    }
				} else if ($f == 'trigger_events') {
					if (!is_array($value)) {
						$this->error_details[] = array('invalid', $f);
					} else {
						$this->manifest->$setter($value);
					}
                } elseif ($f == 'tags') {
                    if (!is_array($value)) {
                        $this->error_details[] = array('invalid', $f);
                    } else {
                        $this->manifest->$setter($value);
                    }
                } elseif ($f == 'api_version') {
                    $value = (int)$value;
                    if ($value != 1) {
                        $this->error_details[] = array('invalid', $f);
                    } else {
                        $this->manifest->$setter($value);
                    }
                } elseif ($f == 'is_native') {
                    $value = (bool)$value;
                    $this->manifest->setIsNative($value);
                } else {
                    if (!is_scalar($value)) {
                        $this->error_details[] = array('invalid', $f);
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
            $getter = Strings::underscoreToCamelCase('get_' . str_replace('.', '_', $f));
            $value = $this->manifest->$getter();

            switch ($f) {
                case 'api_version':
                case 'version':
                    if (!$value) {
                        $this->error_details[] = array('invalid', $f);
                    }
                    break;

                case 'author.email':
                    if (!StringEmail::isValueValid($value)) {
                        $this->error_details[] = array('invalid', $f);
                    }
                    break;

                case 'author.link':
                    if (!preg_match('#^https?://#', $value)) {
                        $this->error_details[] = array('invalid', $f);
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
        $lines = array();
        foreach ($this->error_details as $err) {
            $lines[] = sprintf("[%s] %s", $err[0], $err[1]);
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
