<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\DeskPRO\Mail;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Person;
use DpSys\LowError\SystemErrorHandler;
use Orb\Util\Strings;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * Class Message.
 */
class Message extends \Orb\Mail\Message
{
    /**
     * @var string
     */
    protected $context_id;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface
     */
    protected $template_engine;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var array
     */
    protected $template_vars;

    /**
     * @var null
     */
    protected $set_to = null;

    /**
     * @var Person
     */
    protected $set_to_person = null;

    /**
     * @var \Application\DeskPRO\Entity\Blob[]
     */
    protected $attach_blobs = [];

    /**
     * @var array
     */
    protected $embed_only = [];

    /**
     * @var callable
     */
    protected $body_filter;

    /**
     * Set a context about this message. The mailer might treat it differently.
     */
    public function setContextId($context_id)
    {
        $this->context_id = $context_id;
    }

    /**
     * @return string
     */
    public function getContextId()
    {
        return $this->context_id;
    }

    public function doPrepare()
    {
        if ($this->template) {
            if ($this->set_to) {
                $this->template_vars['to_email']   = $this->set_to['email'];
                $this->template_vars['to_name']    = !empty($this->set_to['name']) ? $this->set_to['name'] : $this->set_to['email'];
                $this->template_vars['to_contact'] = !empty($this->set_to['name']) ? $this->set_to['name'].' <'.$this->set_to['email'].'>' : $this->set_to['email'];

                $skip_check = [
                    // Agent email sent to an unknown email address for agent ticket replies
                    // ("your reply was not accepted because it was sent from an unknown address")
                    'DeskPRO:emails_agent:error-unknown-from.html.twig' => 1,
                    // If you created a new agent after calling the AgentDataServer,
                    // then the repository wont contain the new agent when sending this welcome.
                    'DeskPRO:emails_agent:agent-welcome.html.twig' => 1,
                    // When an agent is created via a usersource, they are not yet in the agent repository
                    'DeskPRO:emails_agent:agent-welcome-usersource.html.twig' => 1,
                    // Server / Test email can be sent to anyone
                    'DeskPRO:emails_agent:test-email.html.twig' => 1,
                ];

                if (strpos($this->template, ':emails_agent:') !== false && !isset($skip_check[$this->template])) {
                    $agent = App::getContainer()->getAgentData()->getByEmail($this->set_to['email']);
                    if (!$agent) {
                        // Not an agent
                        // - Generate error log warning
                        // - Send in error report to us
                        // - Blank out email. We need to send a blank email because
                        // there is no way to "stop" at this late stage (it's too "late" by the time this code gets run)
                        // and if we were to throw an exception, it would cause rollbacks to happen.
                        // - TO DO: Can implement custom swiftmailer classes to allow cancelling of messages so the blank
                        // email isn't sent.

                        $e = new \InvalidArgumentException(
                            "Agent email being sent to a non-agent. Template: {$this->template}, Person: {$this->template_vars['to_contact']}"
                        );
                        SystemErrorHandler::logException($e, true);

                        $this->template        = null;
                        $this->template_vars   = null;
                        $this->template_engine = null;
                        $this->set_to_person   = null;
                        $this->attach_blobs    = null;
                        $this->embed_only      = true;
                        $this->setBody('');
                        $this->setSubject('');
                        $this->getHeaders()->addTextHeader(
                            'X-DeskPRO-Error',
                            "Agent email being sent to a non-agent. Template: {$this->template}, Person: {$this->template_vars['to_contact']}"
                        )
                        ;

                        return;
                    }
                }
            }

            if (!$this->set_to_person && !empty($this->template_vars['to_email'])) {
                $this->set_to_person = App::getOrm()->getRepository(Person::class)->findOneByEmail(
                    $this->template_vars['to_email']
                )
                ;
            }

            $this->template_vars['to_person']       = $this->set_to_person;
            $this->template_vars['person_timezone'] = $this->set_to_person ? $this->set_to_person->getDateTimezone(
            ) : App::getContainer()->getSettingsHandler()->getDefaultTimezone();

            $this->template_vars['site_url']    = App::getContainer()->getBrandSetting('core.site_url');
            $this->template_vars['site_name']   = App::getContainer()->getBrandSetting('core.site_name');
            $this->template_vars['deskpro_url'] = App::getContainer()->getBrandSetting('core.deskpro_url');

            $content = $this->template_engine->render($this->template, $this->template_vars);

            if (strpos($content, '___DP___SUBJECT___SEP___') !== false) {
                list($subject, $body) = explode('___DP___SUBJECT___SEP___', $content, 2);

                // Try to clean up subject from whitespace
                $subject = \Orb\Util\Strings::removeEmptyLines($subject);
                $subject = \Orb\Util\Strings::trimLines($subject);
                $subject = str_replace(["\r\n", "\n"], ' ', $subject);
                $subject = trim($subject);

                // Subjects from the template will be escaped due to auto-escaping in twig
                $subject = html_entity_decode($subject, \ENT_QUOTES, 'UTF-8');

                $body = trim($body);
            } else {
                $subject = '';
                $body    = $content;
            }

            if ($subject) {
                $this->setSubject($subject);
            }

            $body = $this->replaceEmbeds($body);
            if ($this->body_filter) {
                $body = call_user_func($this->body_filter, $body, $this, $this->template_vars, 'text/html');
            }
            $this->setEncoder(\Swift_Encoding::getQpEncoding());
            $this->setBody($body, 'text/html');

            if (
                !$this->set_to_person
                || ($this->set_to_person && !$this->set_to_person->isAgent())
                || ($this->set_to_person && $this->set_to_person->isAgent() && $this->set_to_person->getPref(
                        'agent.enable_plaintext_email'
                    ))
            ) {
                $plaintext = $body;
                $plaintext = str_replace('<!--DP_NEWMSG_AS_NOTE-->', '[DP_NEWMSG_AS_NOTE]', $plaintext);
                $plaintext = str_replace('<!--DP_NEWMSG_AS_REPLY-->', '[DP_NEWMSG_AS_REPLY]', $plaintext);

                $start_pos = strpos($plaintext, '<!--DP_PREVIEW_TEXT_BEGIN-->');
                $end_pos   = strpos($plaintext, '<!--DP_PREVIEW_TEXT_END-->');
                if ($start_pos && $end_pos) {
                    $end_pos_len = strlen('<!--DP_PREVIEW_TEXT_END-->');
                    $plaintext   = Strings::cut($plaintext, $start_pos, $end_pos + $end_pos_len);
                }

            // This is a slow process and can crash on complex documents so
            // prevent running on really long messages
            if (strlen($body) < 512000) {
                try {
                    try {
                        $plaintext = preg_replace(
                                '#<a[^>]+dp-reply-help-link[^>]+>[^<]+</a>#',
                                'https://deskpro.com/go/reply',
                                $plaintext
                            );
                        $converter = new \Html2Text\Html2Text($plaintext, ['width' => 0]);
                        $plaintext = $converter->getText();
                    } catch (\Exception $e) {
                        $plaintext = null;
                    }
                    if ($plaintext) {
                        $this->addPart($plaintext, 'text/plain');
                    }
                } catch (\Exception $e) {
                }

            // fallback on just simple strip tags
            } else {
                $plaintext = str_replace("\n", '', $plaintext);
                $plaintext = str_replace(['<br/>', '<br />', '<p>', '</p>', '<div>'], "\n", $plaintext);
                $plaintext = preg_replace(
                        '#<a[^>]+dp-reply-help-link[^>]+>[^<]+</a>#',
                        'deskpro.com/go/reply',
                        $plaintext
                    );
                $plaintext = Strings::stripTags($plaintext);
                if ($plaintext) {
                    $this->addPart($plaintext, 'text/plain');
                }
            }
            } else {
                if ($this->getContentType() == 'text/html') {
                    $body = $this->getBody();
                    $body = $this->replaceEmbeds($body);
                    $this->setBody($body, 'text/html');
                }
            }
        }

        // Attach blobs
        foreach ($this->attach_blobs as $src => $blob) {
            if (isset($this->embed_only[$src])) {
                continue;
            }

            $type = $blob->getContentType();

            // Bug in attaching message/rfc822 messages
            // results in invalid emails.
            // See open bug: https://github.com/swiftmailer/swiftmailer/issues/258
            if ($type == 'message/rfc822') {
                $type = 'application/octet-stream';
            }

            $this->attach(\Swift_Attachment::newInstance(
                App::getContainer()->getBlobStorage()->copyBlobRecordToString($blob),
                $blob->getFilename(),
                $type
            ));
        }

        // These need to be unset so the message can be properly serialized
        // if it needs to be inserted as a queued message
        $this->template        = null;
        $this->template_vars   = null;
        $this->template_engine = null;
        $this->set_to_person   = null;
        $this->attach_blobs    = null;
        $this->embed_only      = true;

        $this->getHeaders()->addTextHeader('X-DeskPRO-Build', defined('DP_BUILD_TIME') ? DP_BUILD_TIME : 1);
    }

    /**
     * Replaces embeddable attachments with their embedded version.
     *
     * @param string $body
     *
     * @return string
     */
    public function replaceEmbeds($body)
    {
        $self = $this;

        $embed_map = [];
        foreach ($this->attach_blobs as $src => $blob) {
            if (is_int($src)) {
                continue;
            }

            $regex = '#(<img[^>]+src=")'.preg_quote($src, '#').'(\?s=\d+)?("[^>]*>)#i';
            $body  = preg_replace_callback($regex, function ($match) use ($self, &$embed_map, $src, $blob) {
                if (!isset($embed_map[$src])) {
                    // in case the src is referenced twice
                    $embed_map[$src] = $self->embed(\Swift_Image::newInstance(
                        App::getContainer()->getBlobStorage()->copyBlobRecordToString($blob),
                        $blob->getFilename(),
                        $blob->getContentType()
                    ));
                }

                return $match[1].$embed_map[$src].$match[3];
            }, $body);

            // Remove links to inline attachments as well
            $body = preg_replace('#<a[^>]+dp-embed-blob-a-'.preg_quote($blob->getAuthId(), '#').'[^>]*>(<img[^>]+>)</a>#', '$1', $body);
        }

        foreach ($embed_map as $src => $null) {
            // already embedded, don't need to attach again
            unset($self->attach_blobs[$src]);
        }

        return $body;
    }

    /**
     * Attach a blob to the message. If you want it to be embedded,
     * pass the src value of an <img> tag that will hold it. It is recommended
     * that the embed image src is a URL, as it will be left if the embed
     * cannot happen for any reason.
     *
     * @param Blob        $blob
     * @param string|null $embedImageSrc       If non-null/integer, will search the body for this image src to embed
     * @param bool        $includeEmbeddedOnly If true, the file will only be attached if embedded
     */
    public function attachBlob(Blob $blob, $embedImageSrc = null, $includeEmbeddedOnly = false)
    {
        if ($embedImageSrc && !ctype_digit($embedImageSrc)) {
            $this->attach_blobs[$embedImageSrc] = $blob;
            if ($includeEmbeddedOnly) {
                $this->embed_only[$embedImageSrc] = true;
            }
        } else {
            $this->attach_blobs[] = $blob;
        }
    }

    /**
     * Brings in a blob that will be embedded.
     *
     * @param string                           $src  The image src attribute that will be replaced
     * @param \Application\DeskPRO\Entity\Blob $blob
     */
    public function embedImage($src, Blob $blob)
    {
        $this->embed_images[$src] = $blob;
    }

    /**
     * @param EngineInterface $template_engine
     */
    public function setTemplateEngine(EngineInterface $template_engine)
    {
        $this->template_engine = $template_engine;
    }

    /**
     * Set the template we'll use to fetch the subject and body from.
     *
     * @param $name
     * @param array $vars
     */
    public function setTemplate($name, array $vars = [])
    {
        $this->template      = $name;
        $this->template_vars = $vars;
    }

    /**
     * A shortcut to set to and name.
     *
     * @param Person $person
     */
    public function setToPerson(Person $person)
    {
        $this->setTo($person->getPrimaryEmailAddress(), $person->getDisplayName());
        $this->set_to_person = $person;
    }

    /**
     * @param array|string $addresses
     * @param null         $name
     *
     * @return \Swift_Mime_SimpleMessage
     */
    public function setTo($addresses, $name = null)
    {
        if (is_array($addresses)) {
            reset($addresses);
            $this->set_to = [
                'email' => \Orb\Util\Arrays::getFirstKey($addresses),
                'name'  => \Orb\Util\Arrays::getFirstItem($addresses),
            ];
        } else {
            $this->set_to = [
                'name'  => $name,
                'email' => $addresses,
            ];
        }

        return parent::setTo($addresses, $name);
    }

    /**
     * Set a body filter to run the body content through during prepare.
     *
     * Params: string $body, Message $email, array $vars, string $contentType
     *
     * @param callable $fn
     */
    public function setBodyFilter($fn)
    {
        $this->body_filter = $fn;
    }

    /**
     * @static
     *
     * @param null $subject
     * @param null $body
     * @param null $contentType
     * @param null $charset
     *
     * @return \Application\DeskPRO\Mail\Message
     */
    public static function newInstance($subject = null, $body = null, $contentType = null, $charset = null)
    {
        return new static($subject, $body, $contentType, $charset);
    }

    /**
     * @throws \Exception
     *
     * @return string
     */
    public function __toString()
    {
        try {
            return parent::__toString();
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);

            // It's a fatal error either way :(
            throw $e;
        }
    }
}
