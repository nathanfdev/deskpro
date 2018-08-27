<?php

namespace DeskPRO\Bundle\InstallBundle\Installer;

use Symfony\Component\Console\Input\InputInterface;

/**
 * A profile is just pre-answered questions.
 */
class InstallProfile
{
    /**
     * @var array
     */
    private static $questionIds = [
        'db_host',
        'db_user',
        'db_password',
        'db_dbname',
        'path_php',
        'path_mysqldump',
        'path_mysql',
        'web_url',
        'user_name',
        'user_email',
        'user_password',
        'skip_recommendations',
        'filestorage_method',
        'session_uuid',
    ];

    /**
     * @var array
     */
    private static $dbs = [
        'db',
        'system_db',
        'audit_db',
        'voice_db',
    ];

    /**
     * @var array
     */
    private $answers = [];

    /**
     * @return array
     */
    public static function getQuestionIds()
    {
        return self::$questionIds;
    }

    /**
     * @return array
     */
    public static function getDbs()
    {
        return self::$dbs;
    }

    /**
     * @param string $f
     */
    public function readAnswersFile($f)
    {
        if (!file_exists($f)) {
            throw new \InvalidArgumentException('Profile file does not exist: '.$f);
        }

        $data = @json_decode(@file_get_contents($f), true);

        if (!is_array($data)) {
            throw new \InvalidArgumentException('Invalid profile file: '.$f);
        }

        $this->answers = array_merge($this->answers, $this->processAnswers($data));
    }

    /**
     * @param InputInterface $input
     */
    public function readAnswersInput(InputInterface $input)
    {
        $new_answers = [];
        foreach (self::getQuestionIds() as $qid) {
            if ($input->getOption('opt_'.$qid) !== null) {
                $new_answers[$qid] = $input->getOption('opt_'.$qid);
            }
        }

        $this->answers = array_merge($this->answers, $this->processAnswers($new_answers));
    }

    /**
     * @param array $answers
     *
     * @return array
     */
    private function processAnswers(array $answers)
    {
        $new_answers = [];

        foreach ($answers as $k => $v) {
            switch ($k) {
                case 'user':
                    if (isset($v['name'])) {
                        $new_answers['user_name'] = $v['name'];
                    }
                    if (isset($v['email'])) {
                        $new_answers['user_email'] = $v['email'];
                    }
                    if (isset($v['password'])) {
                        $new_answers['user_password'] = $v['password'];
                    }
                    break;
                case 'dbinfo':
                    if (isset($v['host'])) {
                        $new_answers['db_host'] = $v['host'];
                    }
                    if (isset($v['user'])) {
                        $new_answers['db_user'] = $v['user'];
                    }
                    if (isset($v['password'])) {
                        $new_answers['db_password'] = $v['password'];
                    }
                    if (isset($v['dbname'])) {
                        $new_answers['db_dbname'] = $v['dbname'];
                    }
                    break;
                case 'skip_recommendations':
                    $v = strtolower($v);
                    if ($v === 'true' || $v === 't' || $v === 'y' || $v === 'yes' || $v === '1' || $v === 'on') {
                        $new_answers[$k] = $v;
                    }
                    break;
                default:
                    $new_answers[$k] = $v;
            }
        }

        return $new_answers;
    }

    /**
     * @param string $qid
     *
     * @return bool
     */
    public function hasAnswer($qid)
    {
        return array_key_exists($qid, $this->answers);
    }

    /**
     * @param string $qid
     *
     * @return mixed|null
     */
    public function getAnswer($qid)
    {
        if (!isset($this->answers[$qid])) {
            return null;
        }

        return $this->answers[$qid];
    }
}
