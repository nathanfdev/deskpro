<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Twig\Loader;

use Application\DeskPRO\App;

/**
 * Twig loaders expect a path, and to read templates from the database means.
 */
class DbStreamWrapper
{
    /** @var int */
    protected $position;
    /** @var string */
    protected $name;
    /** @var string */
    protected $php_code;
    /** @var int */
    protected $size = 0;
    /** @var int */
    protected $date_updated;

    public static function getTemplateInfo($name)
    {
        static $templates = [];
        static $not_set   = [];

        // Already loaded the template
        if (isset($templates[$name])) {
            if (!isset($templates[$name]['size'])) {
                $templates[$name]['size'] = strlen($templates[$name]['template_compiled']);
            }

            return $templates[$name];
        } elseif (isset($not_set[$name])) {
            return;
        }

        //------------------------------
        // Fetch template info, and try to
        // guess which other templates will be used as well
        //------------------------------

        $parts  = explode(':', $name, 3);
        $bundle = $parts[0];
        $subdir = $parts[1];

        $params   = [$name];
        $params[] = 'DeskPRO:%';
        $params[] = "$bundle:Common:%";
        $params[] = "$bundle:Main:%";

        if ($bundle == 'UserBundle') {
            $params[] = 'UserBundle:Portal:%';
        }

        if ($subdir) {
            $params[] = "$bundle:$subdir:%";
        }

        $where = ['name = ?'];
        for ($i = 1, $c = count($params); $i < $c; ++$i) {
            $where[] = 'name LIKE ?';
        }
        $where = implode(' OR ', $where);

        // no good way to avoid this App:: use at the moment, because we need db access here
        $statement = App::$container->get('doctrine.dbal.default_connection')->executeQuery("
            SELECT name, template_compiled, UNIX_TIMESTAMP(date_updated) AS date_updated
            FROM templates
            WHERE $where
        ", $params);
        $results = [];
        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $results[$row['name']] = $row;
        }

        $templates = array_merge($templates, $results);

        if (!isset($templates[$name])) {
            $not_set[$name] = true;

            return;
        }

        $templates[$name]['size'] = strlen($templates[$name]['template_compiled']);

        return $templates[$name];
    }

    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $this->position = 0;

        if (!preg_match('#/([^/]+)$#', $path, $m)) {
            return false;
        }

        $this->name = $m[1];

        $info = self::getTemplateInfo($this->name);
        if (!$info) {
            return false;
        }

        $this->php_code     = $info['template_compiled'];
        $this->date_updated = $info['date_updated'];
        $this->size         = $info['size'];

        return true;
    }

    public function stream_read($count)
    {
        $ret = substr($this->php_code, $this->position, $count);
        $this->position += strlen($ret);

        return $ret;
    }

    public function stream_write($data)
    {
        $left           = substr($this->php_code, 0, $this->position);
        $right          = substr($this->php_code, $this->position + strlen($data));
        $this->php_code = $left.$data.$right;
        $this->position += strlen($data);

        return strlen($data);
    }

    public function stream_tell()
    {
        return $this->position;
    }

    public function stream_eof()
    {
        return $this->position >= strlen($this->php_code);
    }

    public function stream_seek($offset, $whence)
    {
        switch ($whence) {
            case SEEK_SET:
                if ($offset < strlen($this->php_code) && $offset >= 0) {
                    $this->position = $offset;

                    return true;
                } else {
                    return false;
                }
                break;

            case SEEK_CUR:
                if ($offset >= 0) {
                    $this->position += $offset;

                    return true;
                } else {
                    return false;
                }
                break;

            case SEEK_END:
                if (strlen($this->php_code) + $offset >= 0) {
                    $this->position = strlen($this->php_code) + $offset;

                    return true;
                } else {
                    return false;
                }
                break;

            default:
                return false;
        }
    }

    public function url_stat($path)
    {
        if ($path == 'dptpl://load') {
            return [
                'dev'     => 0,
                'ino'     => 0,
                'mode'    => 040777,
                'nlink'   => 0,
                'uid'     => 0,
                'gid'     => 0,
                'rdev'    => 0,
                'size'    => 1,
                'atime'   => time(),
                'mtime'   => time(),
                'ctime'   => time(),
                'blksize' => 0,
                'blocks'  => -1,
            ];
        }

        if (!preg_match('#/([^/]+)$#', $path, $m)) {
            return false;
        }

        $name = $m[1];
        $info = self::getTemplateInfo($name);

        if (!$info) {
            return false;
        }

        return [
            'dev'     => 0,
            'ino'     => 0,
            'mode'    => 0100555,
            'nlink'   => 0,
            'uid'     => 0,
            'gid'     => 0,
            'rdev'    => 0,
            'size'    => $info['size'],
            'atime'   => time(),
            'mtime'   => $info['date_updated'],
            'ctime'   => $info['date_updated'],
            'blksize' => 0,
            'blocks'  => -1,
        ];
    }

    public function stream_stat()
    {
        return [
            'dev'     => 0,
            'ino'     => 0,
            'mode'    => 0100555,
            'nlink'   => 0,
            'uid'     => 0,
            'gid'     => 0,
            'rdev'    => 0,
            'size'    => $this->size,
            'atime'   => time(),
            'mtime'   => $this->date_updated,
            'ctime'   => $this->date_updated,
            'blksize' => 0,
            'blocks'  => -1,
        ];
    }

    public function stream_metadata($path, $option, $var)
    {
        return true;
    }

    /**
     * Signal that stream_select is not supported by returning false.
     *
     * @param int $cast_as
     *
     * @return bool
     */
    public function stream_cast($cast_as)
    {
        return false;
    }
}
