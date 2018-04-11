<?php

namespace DpSys\LowScript;

use Orb\Util\Strings;

/**
 * A light-weight loader for website widgetss.
 */
class DpScript extends LowScriptAbstract
{
    public function runAction()
    {
        try {
            $pathinfo = $this->getPathInfo();

            if (preg_match('#^/vis\.js#', $pathinfo)) {
                $this->visitorPingAction();
            } elseif (preg_match('#^/chat/is-available\.js#', $pathinfo)) {
                $this->visitorPingAction();
            } elseif (preg_match('#^/request-session\.(json|js)#', $pathinfo)) {
                $this->requestSessionAction();
            } elseif (preg_match('#^/session-ping\.json#', $pathinfo)) {
                $this->sessionPingAction();
            } elseif (preg_match('#^/user-lang-(\d+)\.js#', $pathinfo, $match)) {
                $this->userLanguageAction($match[1]);
            } elseif (preg_match('#^/agent-lang-(\d+)\.js#', $pathinfo, $match)) {
                $this->agentLanguageAction($match[1]);
            } else {
                header('HTTP/1.0 404 Not Found');
                echo 'Action not found. (1)';
            }
        } catch (\Exception $exception) {
            if ($this->dpEnv->isDebug()) {
                echo "\n\n[{$exception->getCode()}] {$exception->getMessage()}\n\n";

                $backtrace = $exception->getTrace();
                $trace     = self::formatBacktrace($backtrace);
                echo $trace;
            }

            $this->handleException($exception);
        }
    }

    //###################################################################################################################
    // visitorPing
    //###################################################################################################################

    protected function visitorPingAction()
    {
        header('Content-Type: text/javascript; filename=vis.js');
        header('Content-Disposition: inline; filename=vis.js');
        header('Last-Modified: '.date('D, d M Y H:i:s', strtotime('-1 year')).' GMT');
        header('Expires: '.date('D, d M Y H:i:s', strtotime('-1 year')).' GMT');
        header('Cache-Control: max-age=0,private');
        echo '// This is a legacy URL';
    }

    /**
     * Check if chat is available / current chat is active.
     *
     * @return string JS string to return
     */
    public function checkChatAvailable(array $visitor = null)
    {
        header('Content-Type: text/javascript; filename=vis.js');
        header('Content-Disposition: inline; filename=vis.js');
        header('Last-Modified: '.date('D, d M Y H:i:s', strtotime('-1 year')).' GMT');
        header('Expires: '.date('D, d M Y H:i:s', strtotime('-1 year')).' GMT');
        header('Cache-Control: max-age=0,private');
        echo '// This is a legacy URL';
    }

    //###################################################################################################################
    // sessionPing
    //###################################################################################################################

    protected function sessionPingAction()
    {
        header('Content-Type: text/javascript; filename=vis.js');
        header('Content-Disposition: inline; filename=vis.js');
        header('Last-Modified: '.date('D, d M Y H:i:s', strtotime('-1 year')).' GMT');
        header('Expires: '.date('D, d M Y H:i:s', strtotime('-1 year')).' GMT');
        header('Cache-Control: max-age=0,private');
        echo '// This is a legacy URL';
    }

    //###################################################################################################################
    // requestSession
    //###################################################################################################################

    protected function requestSessionAction()
    {
        header('Content-Type: text/javascript; filename=vis.js');
        header('Content-Disposition: inline; filename=vis.js');
        header('Last-Modified: '.date('D, d M Y H:i:s', strtotime('-1 year')).' GMT');
        header('Expires: '.date('D, d M Y H:i:s', strtotime('-1 year')).' GMT');
        header('Cache-Control: max-age=0,private');
        echo '// This is a legacy URL';
    }

    //###################################################################################################################
    // agentLanguage
    //###################################################################################################################

    public function agentLanguageAction($language_id)
    {
        $language_id = intval($language_id);
        $no_cache    = !empty($_GET['nocache']);
        $js          = false;
        $cache_file  = dp_get_tmp_dir().'/agent-lang-'.$language_id.'.cache';

        if (!$no_cache) {
            if (file_exists($cache_file)) {
                $data = @unserialize(@file_get_contents($cache_file));
                if (is_string($data)) {
                    $js = trim($data);

                    if (defined('DP_BUILD_TIME')) {
                        $last_line = Strings::getLastLine($js);
                        if ($version = Strings::extractRegexMatch('#DP_BUILD\((.*?)\)#', $last_line)) {
                            if ($version < DP_BUILD_TIME) {
                                $js = null;
                            }
                        }
                    }
                }
            }
        }

        if (!$js) {
            $container = $this->bootFullSystem();

            $tr   = $container->getTranslator();
            $lang = $container->getEm()->getRepository('DeskPRO:Language')->find($language_id);
            if ($lang && $lang->has_agent) {
                $tr->setLanguage($lang);
            } else {
                $language_id = 0;
                $cache_file  = dp_get_tmp_dir().'/agent-lang-'.$language_id.'.cache';
            }

            $js_phrases                                           = [];
            $js_phrases['agent.general.add_a_label']              = $tr->getPhraseText('agent.general.add_a_label');
            $js_phrases['agent.general.on']                       = $tr->getPhraseText('agent.general.on');
            $js_phrases['agent.general.off']                      = $tr->getPhraseText('agent.general.off');
            $js_phrases['agent.general.saving']                   = $tr->getPhraseText('agent.general.saving');
            $js_phrases['agent.general.drop_here_to_attach_file'] = $tr->getPhraseText('agent.general.drop_here_to_attach_file');

            $js_phrases['agent.time.reltime_less_second']    = $tr->getPhraseText('agent.time.reltime_less_second');
            $js_phrases['agent.time.reltime_less_minute']    = $tr->getPhraseText('agent.time.reltime_less_minute');
            $js_phrases['agent.time.reltimeago_less_second'] = $tr->getPhraseText('agent.time.reltimeago_less_second');
            $js_phrases['agent.time.reltimeago_less_minute'] = $tr->getPhraseText('agent.time.reltimeago_less_minute');

            foreach (['reltime', 'reltimeago'] as $pre) {
                foreach (['second', 'minute', 'hour', 'day', 'week', 'month', 'year'] as $name) {
                    $js_phrases["agent.time.{$pre}_1_{$name}"] = $tr->getPhraseText("agent.time.{$pre}_1_{$name}");
                    $js_phrases["agent.time.{$pre}_x_{$name}"] = $tr->getPhraseText("agent.time.{$pre}_x_{$name}");
                }
            }

            $add_phrases = [
                'agent.general.clear',
                'agent.tasks.no_due_date',
                'agent.tasks.no_due_time',
                'agent.userchat.message_started',
                'agent.userchat.transcript_sent',
                'agent.userchat.message_user_joined',
                'agent.userchat.message_user_left',
                'agent.userchat.message_set_department',
                'agent.userchat.assigned_to',
                'agent.userchat.msg_new_user_track',
                'agent.userchat.unassigned',
                'agent.userchat.msg_agent_timeout',
                'agent.userchat.msg_user_timeout',
                'agent.userchat.ended_by',
                'agent.userchat.message_ended-by',
                'agent.userchat.message_ended-by-user',
                'agent.userchat.ended_user',
                'agent.userchat.message_started',
                'agent.userchat.message_user-joined',
                'agent.userchat.message_user-left',
                'agent.userchat.message_user-returned',
                'agent.userchat.message_set-department',
                'agent.userchat.message_assigned',
                'agent.userchat.message_unassigned',
                'agent.userchat.message_agent-timeout',
                'agent.userchat.message_wait-timeout',
                'agent.userchat.message_user-timeout',
                'agent.userchat.message_ended',
            ];

            foreach ($add_phrases as $k) {
                $js_phrases[$k] = $tr->getPhraseText($k);
            }

            $js = 'window.DESKPRO_LANG = '.json_encode($js_phrases).';';
            if (defined('DP_BUILD_TIME')) {
                $js .= "\n/* DP_BUILD(".DP_BUILD_TIME.") */\n";
            }

            if (!$no_cache) {
                $cache_slam_file = $cache_file.'.slam';
                if (!file_exists($cache_slam_file) || time() - filemtime($cache_slam_file) > 30) {
                    $slam_fp = @fopen($cache_slam_file, 'w');
                    if ($slam_fp && @flock($slam_fp, \LOCK_EX)) {
                        @file_put_contents($cache_file, serialize($js), \LOCK_EX);
                        @chmod($cache_file, 0777);
                        @flock($slam_fp, \LOCK_UN);
                        @fclose($slam_fp);
                        @unlink($cache_slam_file);
                    } else {
                        @fclose($slam_fp);
                    }
                }
            }
        }

        header('Content-Type: application/javascript; charset=utf-8');
        header('Content-Length: '.strlen($js));
        header('Content-Disposition: inline; filename=agent-lang-'.$language_id.'.js');
        header('Last-Modified: '.date('D, d M Y H:i:s', strtotime('-1 year')).' GMT');
        header('Expires: '.date('D, d M Y H:i:s', strtotime('+1 year')).' GMT');
        header('X-Content-Type-Options: nosniff');
        echo $js;
    }

    public function userLanguageAction($language_id)
    {
        $language_id = intval($language_id);
        $no_cache    = !empty($_GET['nocache']);
        $js          = false;
        $cache_file  = dp_get_tmp_dir().'/user-lang-'.$language_id.'.cache';

        if (!$no_cache) {
            if (file_exists($cache_file)) {
                $data = @unserialize(@file_get_contents($cache_file));
                if (is_string($data)) {
                    $js = $data;

                    if (defined('DP_BUILD_TIME')) {
                        $last_line = Strings::getLastLine($js);
                        if ($version = Strings::extractRegexMatch('#DP_BUILD\((.*?)\)#', $last_line)) {
                            if ($version < DP_BUILD_TIME) {
                                $js = null;
                            }
                        }
                    }
                }
            }
        }

        if (!$js) {
            $container = $this->bootFullSystem();

            $tr   = $container->getTranslator();
            $lang = $container->getEm()->getRepository('DeskPRO:Language')->find($language_id);
            if ($lang) {
                $tr->setLanguage($lang);
            } else {
                $language_id = 0;
                $cache_file  = dp_get_tmp_dir().'/user-lang-'.$language_id.'.cache';
            }

            $js_phrases = [];

            $js_phrases['user.time.time_less_second']     = $tr->phrase('user.time.time_less_second');
            $js_phrases['user.time.time-ago_less_second'] = $tr->phrase('user.time.time_less_second');

            foreach (['time', 'time-ago'] as $pre) {
                foreach (['second', 'minute', 'hour', 'day', 'week', 'month', 'year'] as $name) {
                    $js_phrases["user.time.{$pre}_1_{$name}"] = $tr->phrase("user.time.{$pre}_1_{$name}");
                    $js_phrases["user.time.{$pre}_x_{$name}"] = $tr->phrase("user.time.{$pre}_x_{$name}");
                }
            }

            $add_phrases = [
                'user.chat.email',
                'user.chat.ended-no-agent',
                'user.chat.error',
                'user.chat.form_chat_button-submit',
                'user.chat.form_chat_send-file',
                'user.chat.form_create_button-submit',
                'user.chat.form_create_department',
                'user.chat.form_create_title',
                'user.chat.form_feedback_button-submit',
                'user.chat.form_feedback_comments',
                'user.chat.form_feedback_rate-satisfaction',
                'user.chat.form_feedback_rate-satisfied',
                'user.chat.form_feedback_rate-time',
                'user.chat.form_feedback_rate-unsatisfied',
                'user.chat.form_feedback_title',
                'user.chat.form_feedback_transcript-email',
                'user.chat.log-title',
                'user.chat.log_chat-id',
                'user.chat.log_created-date',
                'user.chat.log_fields_agent',
                'user.chat.log_fields_department',
                'user.chat.log_message_author-you',
                'user.chat.log_nav-view-chats',
                'user.chat.log_no_department',
                'user.chat.log_unassigned',
                'user.chat.message_agent-timeout',
                'user.chat.message_assigned',
                'user.chat.message_chatting-with',
                'user.chat.message_ended',
                'user.chat.message_ended-by',
                'user.chat.message_ended-by-user',
                'user.chat.message_finding-agent',
                'user.chat.message_long-wait',
                'user.chat.message_set-department',
                'user.chat.message_started',
                'user.chat.message_unassigned',
                'user.chat.message_uploading',
                'user.chat.message_user-joined',
                'user.chat.message_user-left',
                'user.chat.message_user-returned',
                'user.chat.message_user-timeout',
                'user.chat.message_wait',
                'user.chat.message_wait-timeout',
                'user.chat.name',
                'user.chat.submit-ticket-button',
                'user.chat.submit-ticket-title',
                'user.chat.transcript_sent',
                'user.chat.window_cancel',
                'user.chat.window_cancel-confirm',
                'user.chat.window_close',
                'user.chat.window_close_only',
                'user.chat.window_end-chat',
                'user.chat.window_offline-button',
                'user.chat.window_open-new',
                'user.chat.window_resume-button',
                'user.chat.window_start-button',
                'user.chat.window_upload-drag',
            ];

            foreach ($add_phrases as $k) {
                $js_phrases[$k] = $tr->getPhraseText($k);
            }

            $js = 'window.DESKPRO_LANG = '.json_encode($js_phrases).';';
            if (defined('DP_BUILD_TIME')) {
                $js .= "\n/* DP_BUILD(".DP_BUILD_TIME.") */\n";
            }

            if (!$no_cache) {
                $cache_slam_file = $cache_file.'.slam';
                if (!file_exists($cache_slam_file) || time() - filemtime($cache_slam_file) > 30) {
                    $slam_fp = @fopen($cache_slam_file, 'w');
                    if ($slam_fp && @flock($slam_fp, \LOCK_EX)) {
                        @file_put_contents($cache_file, serialize($js), \LOCK_EX);
                        @chmod($cache_file, 0777);
                        @flock($slam_fp, \LOCK_UN);
                        @fclose($slam_fp);
                        @unlink($cache_slam_file);
                    } else {
                        @fclose($slam_fp);
                    }
                }
            }
        }

        header('Content-Type: application/javascript; charset=utf-8');
        header('Content-Length: '.strlen($js));
        header('Content-Disposition: inline; filename=user-lang-'.$language_id.'.js');
        header('Last-Modified: '.date('D, d M Y H:i:s', strtotime('-1 year')).' GMT');
        header('Expires: '.date('D, d M Y H:i:s', strtotime('+1 year')).' GMT');
        header('X-Content-Type-Options: nosniff');
        echo $js;
    }
}
