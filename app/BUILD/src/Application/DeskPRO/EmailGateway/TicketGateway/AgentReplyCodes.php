<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\TicketGateway;

use Application\DeskPRO\App;
use Application\DeskPRO\EmailGateway\PersonFromEmailProcessor;
use Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress;
use Orb\Input\Cleaner\Cleaner;
use Orb\Log\Loggable;
use Orb\Log\Logger;
use Orb\Util\Arrays;
use Orb\Util\Numbers;
use Orb\Util\Strings;
use Orb\Validator\StringEmail;

/**
 * Class AgentReplyCodes.
 */
class AgentReplyCodes implements Loggable
{
    /**
     * @var \Orb\Log\Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $orig_body;

    /**
     * @var bool
     */
    protected $is_html = false;

    /**
     * @var string
     */
    protected $new_body;

    /**
     * @var array
     */
    protected $props;

    /**
     * @var Cleaner
     */
    protected $cleaner;

    /**
     * @param string $body
     * @param bool   $is_html
     */
    public function __construct($body, $is_html)
    {
        $this->orig_body = $body;
        $this->is_html   = $is_html;
    }

    /**
     * @param Cleaner $cleaner
     */
    public function setCleaner(Cleaner $cleaner)
    {
        $this->cleaner = $cleaner;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        if ($this->props !== null) {
            return $this->props;
        }

        $this->new_body = $this->orig_body;
        $this->props    = [];

        $this->getLogger()->logInfo(sprintf('[AgentReplyCodes] Getting properties from body of %d bytes (%s)', strlen($this->orig_body), $this->is_html ? 'html' : 'plaintext'));

        $text = $this->orig_body;
        if ($this->is_html) {
            if ($this->cleaner) {
                $text            = $this->cleaner->clean($text, 'html_email_preclean');
                $text            = $this->cleaner->clean($text, 'html_email_basicclean');
                $text            = $this->cleaner->clean($text, 'html_email');
                $text            = Strings::trimHtmlAdvanced($text);
                $text            = $this->cleaner->clean($text, 'html_email_postclean');
                $this->orig_body = $text;
            }

            $text = Strings::standardEol($text);
            $text = str_replace("\n", ' ', $text);
            $text = preg_replace('#<br\s*/?>#', "<br/>\n", $text);
            $text = preg_replace('#(<div[^>]+>)#', "$1\n", $text);
            $text = preg_replace('#(<p[^>]+>)#', "$1\n", $text);
            $text = preg_replace('#</div>#', "</div>\n", $text);
            $text = preg_replace('#</p>#', "</p>\n", $text);
            $text = Strings::stripTags($text);
        }

        $text = Strings::standardEol($text);
        $text = Strings::trimLines($text);
        $text = explode("\n", $text);
        $text = Arrays::removeFalsey($text);

        foreach ($text as $l) {
            if (!preg_match('/^#([a-z_\-]+)\s*(.*?)$/i', $l, $m)) {
                break;
            }

            $this->getLogger()->logInfo(sprintf('[AgentReplyCodes] Got code: #%s %s', $m[1], $m[2]));

            $code  = strtolower($m[1]);
            $code  = preg_replace('#[^a-z]#i', '', $code);
            $param = Strings::trimWhitespace($m[2]);

            $for_codepos  = Strings::trimWhitespace($m[1]);
            $for_parampos = Strings::trimWhitespace($m[2]);

            if (!$this->handleCode($code, $param)) {
                $this->getLogger()->logInfo(sprintf('[AgentReplyCodes] -- Invalid code'));
                break;
            }

            // Need to cut out this text from the body
            if ($this->is_html) {
                // With HTML we have to try and find the tokens individually
                // incase there is markup between the code and param. e.g.:
                // #assign <span>chris.nadeau@deskpro.com</span>
                $param_pos = null;
                $code_pos  = null;
                if (($code_pos = strpos($this->new_body, "#$for_codepos")) !== false && (!$for_parampos || ($param_pos = strpos($this->new_body, $for_parampos, $code_pos)) !== false)) {
                    if ($param_pos) {
                        $a_pos = strpos($this->new_body, '<a', $code_pos);
                        if ($a_pos !== false && $a_pos < $param_pos) {
                            $new_param_pos = strpos($this->new_body, $for_parampos, $param_pos + 1);
                            if ($new_param_pos !== false) {
                                $param_pos = $new_param_pos;
                            }
                        }

                        $this->new_body = Strings::cut($this->new_body, $code_pos, $param_pos + strlen($for_parampos));
                    } else {
                        $this->new_body = Strings::cut($this->new_body, $code_pos, $code_pos + strlen($for_codepos) + 1);
                    }

                    // Then use a random token so we can anchor a regex to remove surrounding whitespace easily
                    $tok            = '__'.Strings::random(10, Strings::CHARS_ALPHANUM_IU).'__';
                    $this->new_body = Strings::inject($this->new_body, $tok, $code_pos);
                    $this->new_body = preg_replace("#\\s*$tok\\s*#s", '', $this->new_body);
                } else {
                    $this->getLogger()->logDebug('[AgentReplyCodes] Could not find tokens to clean: '.$m[0]);
                }
            } else {
                $this->new_body = Strings::strReplaceOne($m[0], '', $this->new_body);
            }
        }

        return $this->props;
    }

    /**
     * @return string
     */
    public function getNewBody()
    {
        return trim($this->new_body);
    }

    /**
     * Get the original body that we scanned for codes. This is the original body string but may be
     * slightly modified by the initial cleaner.
     *
     * @return string
     */
    public function getOrigBody()
    {
        return $this->orig_body;
    }

    /**
     * @param string $code
     * @param string $param
     *
     * @return bool True if is a valid code SYNTAX
     */
    protected function handleCode($code, $param)
    {
        switch ($code) {
            case 'awaitingagent':
                $this->getLogger()->logDebug('[AgentReplyCodes] Status = awaiting_agent');
                $this->props['status'] = 'awaiting_agent';
                break;

            case 'awaitinguser':
                $this->getLogger()->logDebug('[AgentReplyCodes] Status = awaiting_user');
                $this->props['status'] = 'awaiting_user';
                break;

            case 'resolved':
                $this->getLogger()->logDebug('[AgentReplyCodes] Status = awaiting_resolved');
                $this->props['status'] = 'resolved';
                break;

            case 'hold':
                $this->getLogger()->logDebug('[AgentReplyCodes] Enable hold');
                $this->props['status']  = 'awaiting_agent';
                $this->props['is_hold'] = true;
                break;

            case 'unhold':
                $this->getLogger()->logDebug('[AgentReplyCodes] Disable hold');
                $this->props['status']  = 'awaiting_agent';
                $this->props['is_hold'] = false;
                break;

            case 'status':
                $param = preg_replace('#[^a-z]#i', '', $param);
                $param = strtolower($param);

                switch ($param) {
                    case 'agent':
                    case 'awaitingagent':
                        $this->getLogger()->logDebug('[AgentReplyCodes] Set status to awaiting_agent');
                        $this->props['status'] = 'awaiting_agent';
                        break;

                    case 'user':
                    case 'awaitinguser':
                        $this->getLogger()->logDebug('[AgentReplyCodes] Set status to awaiting_user');
                        $this->props['status'] = 'awaiting_user';
                        break;

                    case 'resolved':
                        $this->getLogger()->logDebug('[AgentReplyCodes] Set status to resolved');
                        $this->props['status'] = 'resolved';
                        break;

                    case 'hold':
                        $this->getLogger()->logDebug('[AgentReplyCodes] Set status to awaiting_agent with hold');
                        $this->props['status']  = 'awaiting_agent';
                        $this->props['is_hold'] = true;
                        break;

                    default:
                        $this->getLogger()->logDebug('[AgentReplyCodes] Unknown set status');
                        break;
                }
                break;

            case 'urgency':
                $urg = (int) Strings::extractRegexMatch('#(\d+)#', $param);
                if ($urg && Numbers::inRange($urg, 1, 10)) {
                    $this->getLogger()->logDebug('[AgentReplyCodes] Set urgency to '.$urg);
                    $this->props['urgency'] = $urg;
                } else {
                    $this->getLogger()->logDebug('[AgentReplyCodes] Invalid urgency option: '.$param);
                }
                break;

            case 'note':
            case 'isnote':
            $this->getLogger()->logDebug('[AgentReplyCodes] Message is a note');
                $this->props['is_note'] = true;
                break;

            case 'reply':
            case 'isreply':
                $this->getLogger()->logDebug('[AgentReplyCodes] Message is a reply');
                $this->props['is_reply'] = true;
                break;

            case 'assign':
            case 'agent':
                $this->getLogger()->logDebug('[AgentReplyCodes] Assign agent -- finding param: '.$param);
                $agent = $this->_findAgentFromString($param);
                if ($agent) {
                    $this->getLogger()->logDebug('[AgentReplyCodes] Assign agent '.$agent->id);
                    $this->props['assign_agent'] = $agent;
                } else {
                    $this->getLogger()->logDebug('[AgentReplyCodes] Could not find agent: '.$param);
                }
                break;

            case 'follow':
            case 'follower':
            case 'addfollower':
                $this->getLogger()->logDebug('[AgentReplyCodes] Add follower -- finding param: '.$param);
                $agent = $this->_findAgentFromString($param);
                if ($agent) {
                    $this->getLogger()->logDebug('[AgentReplyCodes] Add follower: '.$agent->id);
                    if (empty($this->props['add_followers'])) {
                        $this->props['add_followers'] = [];
                    }
                    $this->props['add_followers'][] = $agent;
                } else {
                    $this->getLogger()->logDebug('[AgentReplyCodes] Could not find agent: '.$param);
                }
                break;

            case 'unfollow':
            case 'removefollower':
                $this->getLogger()->logDebug('[AgentReplyCodes] Remove follower -- finding param: '.$param);
                $agent = $this->_findAgentFromString($param);
                if ($agent) {
                    $this->getLogger()->logDebug('[AgentReplyCodes] Remove follower: '.$agent->id);
                    if (empty($this->props['remove_followers'])) {
                        $this->props['remove_followers'] = [];
                    }
                    $this->props['remove_followers'][] = $agent;
                } else {
                    $this->getLogger()->logDebug('[AgentReplyCodes] Could not find agent: '.$param);
                }
                break;

            case 'user':
                if (StringEmail::isValueValid($param)) {
                    $person_processor = new PersonFromEmailProcessor();
                    $email            = new EmailAddress();
                    $email->email     = $param;
                    $email->name      = '';

                    $person = $person_processor->findPerson($email);

                    if ($person) {
                        $this->getLogger()->logDebug('[AgentReplyCodes] Set user to '.$person->id);
                        $this->props['user'] = $person;
                    } else {
                        $this->getLogger()->logDebug('[AgentReplyCodes] Creating new person: '.$param);
                        $person = $person_processor->createPerson($email);
                        $this->getLogger()->logDebug('[AgentReplyCodes] Creating person #'.$person->id);
                        $this->props['user'] = $person;
                    }
                } else {
                    $this->getLogger()->logDebug('[AgentReplyCodes] User must be an email: '.$param);
                }
                break;

            case 'team':
            case 'assignteam':
                $test_param = preg_replace('#\s#', '', $param);
                $test_param = strtolower($test_param);

                $use_team = null;
                foreach (App::getDataService('AgentTeam')->getTeams() as $team) {
                    $test_name = preg_replace('#\s#', '', $team->name);
                    $test_name = strtolower($test_name);

                    if ($test_name == $test_param) {
                        $use_team = $team;
                        break;
                    }
                }

                if ($use_team) {
                    $this->getLogger()->logDebug('[AgentReplyCodes] Assign team '.$use_team->id);
                    $this->props['assign_agent_team'] = $use_team;
                } else {
                    $this->getLogger()->logDebug('[AgentReplyCodes] Unknown agent team: '.$param);
                }

                break;

            case 'label':
            case 'labels':
                $param = explode(',', $param);
                $param = Arrays::func($param, 'trim');
                $param = Arrays::removeFalsey($param);

                if ($param) {
                    if (!isset($this->props['labels'])) {
                        $this->props['labels'] = [];
                    }

                    $this->getLogger()->logDebug('[AgentReplyCodes] Add labels: '.implode(', ', $param));

                    $this->props['labels'] = array_merge($this->props['labels'], $param);
                    $this->props['labels'] = array_unique($this->props['labels']);
                }
                break;

            case 'dep':
            case 'department':
                $deps = [];
                foreach (App::getOrm()->getRepository('DeskPRO:Department')->findAll() as $dep) {
                    if ($dep->is_tickets_enabled) {
                        $deps[$dep->id] = $dep;
                    }
                }
                $obj = $this->_findObjFromCollection(
                    $deps,
                    'title',
                    $param
                );

                if ($obj) {
                    $this->getLogger()->logDebug('[AgentReplyCodes] Set department: '.$obj->id);
                    $this->props['department'] = $obj;
                } else {
                    $this->getLogger()->logDebug('[AgentReplyCodes] Unknown department: '.$param);
                }
                break;

            case 'cat':
            case 'category':
                $obj = $this->_findObjFromCollection(
                    App::getOrm()->getRepository('DeskPRO:TicketCategory')->findAll(),
                    'title',
                    $param
                );

                if ($obj) {
                    $this->getLogger()->logDebug('[AgentReplyCodes] Set category: '.$obj->id);
                    $this->props['category'] = $obj;
                } else {
                    $this->getLogger()->logDebug('[AgentReplyCodes] Unknown category: '.$param);
                }
                break;

            case 'pri':
            case 'priority':
                $obj = $this->_findObjFromCollection(
                    App::getOrm()->getRepository('DeskPRO:TicketPriority')->findAll(),
                    'title',
                    $param
                );

                if ($obj) {
                    $this->getLogger()->logDebug('[AgentReplyCodes] Set priority: '.$obj->id);
                    $this->props['priority'] = $obj;
                } else {
                    $this->getLogger()->logDebug('[AgentReplyCodes] Unknown priority: '.$param);
                }
                break;

            case 'work':
            case 'workflow':
                $obj = $this->_findObjFromCollection(
                    App::getOrm()->getRepository('DeskPRO:TicketWorkflow')->findAll(),
                    'title',
                    $param
                );

                if ($obj) {
                    $this->getLogger()->logDebug('[AgentReplyCodes] Set workflow: '.$obj->id);
                    $this->props['workflow'] = $obj;
                } else {
                    $this->getLogger()->logDebug('[AgentReplyCodes] Unknown workflow: '.$param);
                }
                break;

            case 'prod':
            case 'product':
                $obj = $this->_findObjFromCollection(
                    App::getOrm()->getRepository('DeskPRO:Product')->findAll(),
                    'title',
                    $param
                );

                if ($obj) {
                    $this->getLogger()->logDebug('[AgentReplyCodes] Set product: '.$obj->id);
                    $this->props['product'] = $obj;
                } else {
                    $this->getLogger()->logDebug('[AgentReplyCodes] Unknown product: '.$param);
                }
                break;

            case 'noreply':
                $this->props['no_reply'] = true;
                break;

            default:
                // check if its a ticket field
                $fields = App::getSystemService('ticket_fields_manager')->getFields();
                $field  = null;
                foreach ($fields as $test_field) {
                    $test_name = $test_field->title;
                    $test_name = strtolower($test_name);
                    $test_name = preg_replace('#[^a-z0-9]#i', '', $test_name);

                    if ($code == "field{$test_field->id}" || $code == $test_name) {
                        $field = $test_field;
                        break;
                    }
                }

                // Dunno what $code is, so failed
                if (!$field) {
                    return false;
                }

                if (!isset($this->props['ticket_fields'])) {
                    $this->props['ticket_fields'] = [];
                }

                // Choice fields means we need to find the actual option...
                if ($field->isChoiceType()) {
                    $child_opt = $this->_findObjFromCollection(
                        $field->children,
                        'title',
                        $param
                    );

                    if ($child_opt) {
                        $set   = isset($this->props['ticket_fields'][$field->id]) ? $this->props['ticket_fields'][$field->id] : [];
                        $set[] = $child_opt->id;
                        $set   = array_unique($set);

                        $this->props['ticket_fields'][$field->id] = $set;
                    }

                // Regular field with a text value
                } else {
                    $this->props['ticket_fields'][$field->id] = $param;
                }

                break;
        }

        return true;
    }

    /**
     * @param string $param
     *
     * @return \Application\DeskPRO\Entity\Person|null
     */
    protected function _findAgentFromString($param)
    {
        if (StringEmail::isValueValid($param)) {
            $agent = App::getDataService('Agent')->getByEmail($param);
            if ($agent) {
                return $agent;
            } else {
                return;
            }
        } else {
            $testParam = $this->prepareCompareString($param);
            $useAgent  = null;

            foreach (App::getDataService('Agent')->getAgents() as $agent) {
                $testName = $this->prepareCompareString($agent['name']);
                if (!$testName) {
                    continue;
                }

                if ($testName == $testParam) {
                    $useAgent = $agent;
                    break;
                }
            }

            if ($useAgent) {
                return $useAgent;
            } else {
                return;
            }
        }
    }

    /**
     * @param array  $collection
     * @param string $nameField
     * @param string $param
     *
     * @return mixed
     */
    protected function _findObjFromCollection($collection, $nameField, $param)
    {
        $testParam = $this->prepareCompareString($param);

        foreach ($collection as $obj) {
            if (!isset($obj[$nameField]) || !$obj[$nameField]) {
                continue;
            }

            $testName = $this->prepareCompareString($obj[$nameField]);
            if (!$testName) {
                continue;
            }

            if ($testParam == $testName) {
                return $obj;
            }
        }

        return;
    }

    /**
     * @param $string
     *
     * @return string
     */
    protected function prepareCompareString($string)
    {
        if (!$string || !is_scalar($string)) {
            return '';
        }

        $string = preg_replace('#\s#', '', $string);
        $string = preg_replace('/[[:punct:]]/', '', $string);
        $string = strtolower($string);

        return $string;
    }

    /**
     * Set the logger.
     *
     * @param \Orb\Log\Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return \Orb\Log\Logger
     */
    public function getLogger()
    {
        if (!$this->logger) {
            $this->logger = new Logger();
        }

        return $this->logger;
    }
}
