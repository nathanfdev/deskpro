<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageAttribute;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageVoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use DeskPRO\Component\Util\RegexUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\PersistentCollection;
use Orb\Html\Html2Text;
use Orb\Util\Strings;
use Orb\Util\Util;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ticket messages.
 *
 * @property int                                $id
 * @property Ticket                             $ticket
 * @property Person                             $person
 * @property EmailSource                        $email_source
 * @property TicketAttachment[]|ArrayCollection $attachments
 * @property \DateTime                          $date_created
 * @property bool                               $is_agent_note
 * @property string                             $creation_system
 * @property string                             $ip_address
 * @property string                             $geo_country
 * @property string                             $email
 * @property string                             $message_hash
 * @property TicketMessageTranslated            $primary_translation
 * @property string                             $message
 * @property string                             $message_full
 * @property string                             $message_raw
 * @property bool                               $show_full_hint
 * @property string                             $lang_code
 *
 * @AppAssert\Ticket\TicketDupeMessage()
 * @AppAssert\Ticket\TicketOpenedMessage()
 */
class TicketMessage extends DomainObject
{
    const CREATED_WEB_PERSON        = 'web.person';
    const CREATED_WEB_PERSON_PORTAL = 'web.person.portal';
    const CREATED_WEB_AGENT         = 'web.agent';
    const CREATED_WEB_AGENT_PORTAL  = 'web.agent.portal';
    const CREATED_WEB_API           = 'web.api';
    const CREATED_MOBILE_AGENT      = 'web.api.mobile.agent';
    const CREATED_MOBILE_PERSON     = 'web.api.mobile.person';
    const CREATED_GATEWAY_PERSON    = 'gateway.person';
    const CREATED_GATEWAY_AGENT     = 'gateway.agent';

    /**
     * The unique id of message.
     *
     * @var int
     */
    protected $id = null;

    /**
     * Ticket with which this message is associated.
     *
     * @var Ticket
     */
    protected $ticket = null;

    /**
     * Person this message was sent by.
     *
     * @var Person
     */
    protected $person = null;

    /**
     * Info about email source, if message comes from such source.
     *
     * @var EmailSource
     */
    protected $email_source = null;

    /**
     * @var TicketMessageAttribute[]
     */
    protected $attributes;

    /**
     * Items attached to the ticket.
     *
     * @var TicketAttachment[]
     *
     * @Assert\Valid()
     */
    protected $attachments;

    /**
     * Date when message was created.
     *
     * @var \DateTime
     */
    protected $date_created;

    /**
     * Is this message agent note?
     *
     * @var bool
     */
    protected $is_agent_note = false;

    /**
     * How this message was created.
     *
     * @var string
     */
    protected $creation_system = 'web';

    /**
     * An ip address from which message was sent.
     *
     * @var string
     */
    protected $ip_address = '';

    /**
     * Unique ID of visitor left this message.
     *
     * @var string
     */
    protected $visitor_id;

    /**
     * Host from which message was left.
     *
     * @var string
     */
    protected $hostname = '';

    /**
     * Country message is from.
     *
     * @var string
     */
    protected $geo_country = null;

    /**
     * The email address the user sent the email from (gateway messages only).
     * This is a perm record and doesnt change even if the user changes/deletes their email
     * address.
     *
     * @var string
     */
    protected $email = '';

    /**
     * An unique hash of message.
     *
     * @var string
     */
    protected $message_hash;

    /**
     * The primary translation is the one sent to the user.
     *
     * @var TicketMessageTranslated
     */
    protected $primary_translation;

    /**
     * The message, will be in HTML!
     *
     * @var string
     *
     * @Assert\NotBlank()
     */
    protected $message = '';

    /**
     * This is the full message, including all quotes/cut content.
     * This will still be the HTMLPurifier'ed content (so it's safe),
     * it's just the message before it's been run through the cutter.
     *
     * @var string
     */
    protected $message_full = null;

    /**
     * This is the full raw message content. It has not been passed through
     * any HTML cleaning process.s.
     *
     * @var string
     */
    protected $message_raw = null;

    /**
     * A hint to say if we should show message_full by default. We do this when
     * we detect that the user has replied to a message inline rather than above the cut line.
     *
     * @var bool
     */
    protected $show_full_hint = false;

    /**
     * The set/detected lang code.
     *
     * @var string
     */
    protected $lang_code = null;

    /**
     * If the message was created from an email just now, then this is the reader.
     *
     * @var \Application\DeskPRO\EmailGateway\Reader\AbstractReader
     */
    public $email_reader;

    /**
     * @var string
     */
    public $withNewSubject = '';

    /**
     * @var null|int
     */
    protected $_message_length = null;

    /**
     * @var string
     */
    protected $originalMessage;

    /**
     * @var TicketMessageEmailId[]
     */
    protected $email_message_id;

    /**
     * TicketMessage constructor.
     *
     * @param null $email_id
     */
    public function __construct($email_id = null)
    {
        $this->setModelField('date_created', new \DateTime());
        $this->attributes  = new ArrayCollection();
        $this->attachments = new ArrayCollection();
        if ($email_id) {
            $ref             = new TicketMessageEmailId();
            $ref['email_id'] = $email_id;
            $ref->message    = $this;
            $this->setModelField('email_message_id', new ArrayCollection([$ref]));
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param Ticket $ticket
     *
     * @return $this
     */
    public function setTicket(Ticket $ticket)
    {
        $this->setModelField('ticket', $ticket);

        return $this;
    }

    /**
     * @param $id
     */
    public function setTicketId($id)
    {
        $this->setModelField('ticket', App::getEntityRepository('DeskPRO:Ticket')->find($id));
    }

    /**
     * @return Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @return int|mixed
     */
    public function getTicketId()
    {
        return $this->ticket['id'];
    }

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function setPerson(Person $person = null)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * @param $id
     *
     * @return $this
     */
    public function setPersonId($id)
    {
        $this->setModelField('person', App::getEntityRepository('DeskPRO:Person')->find($id));

        return $this;
    }

    /**
     * @param bool $is_agent_note
     *
     * @return $this
     */
    public function setAsAgentNote($is_agent_note)
    {
        $this->setModelField('is_agent_note', $is_agent_note);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPersonId()
    {
        return $this->person ? $this->person->getId() : null;
    }

    /**
     * @param $system
     *
     * @return $this
     */
    public function setCreationSystem($system)
    {
        $this->setModelField('creation_system', $system);

        return $this;
    }

    /**
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * @param $hostname
     *
     * @return $this
     */
    public function setHostname($hostname)
    {
        $this->setModelField('hostname', $hostname);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMessageLength()
    {
        if ($this->_message_length !== null) {
            return $this->_message_length;
        }

        $this->_message_length = strlen(strip_tags($this->message));

        return $this->_message_length;
    }

    /**
     * @param int    $max_length
     * @param string $ellipses
     *
     * @return bool|mixed|string
     */
    public function getMessagePreviewText($max_length = 0, $ellipses = '...')
    {
        $message = $this->message;
        $message = RegexUtils::safePregReplace(
            '#\[attach:([a-zA-Z0-9\-_\.]+):([a-zA-Z0-9\-_\.]+):([a-zA-Z0-9\-_\. ]+)\]#',
            '',
            $message
        );

        $sig_pos = strpos($message, '<div class="dp-signature-start">');

        if ($sig_pos !== false) {
            $message = substr($message, 0, $sig_pos);
        }

        $message = Strings::standardEol($message);
        $message = Strings::decodeHtmlEntities($message);
        $message = str_replace(['<br/>', '<br>', '<br />', '<p>', '</p>'], "\n", $message);
        $message = Strings::stripTags($message);
        $message = Strings::decodeWhitespaceHtmlEntities($message);
        $message = preg_replace('#\s+#u', ' ', $message);
        $message = trim($message);

        if ($max_length && isset($message[$max_length])) {
            $message = mb_substr($message, 0, $max_length);
            $message = trim($message);
            $message .= $ellipses;
        }

        return $message;
    }

    /**
     * @param $max_length
     *
     * @return bool|mixed|string
     */
    public function getMessageHtmlClipped($max_length)
    {
        $message = $this->getMessageHtml();
        if (strlen($message) <= $max_length) {
            return $message;
        }

        $message = substr($message, 0, $max_length);

        // Just closes tags we might have chopped up
        $message = App::getContainer()->getInputCleaner()->clean($message, 'html_fix');

        return $message;
    }

    /**
     * @param bool $resizeInlines
     *
     * @return string
     */
    public function getMessageHtml($resizeInlines = true)
    {
        return $this->procInlineAttach($this->message, $resizeInlines);
    }

    /**
     * @param string $message
     * @param bool   $resizeInlines
     *
     * @return string
     */
    public function procInlineAttach($message, $resizeInlines = true)
    {
        // An email might have inline attachments and we tokenize them with these
        // codes so we can now turn them into inline images or attachment links
        $fn = function ($m, $before = '') use ($resizeInlines) {
            $download_url = App::getRouter()->generate(
                'serve_blob',
                ['blob_auth_id' => $m[2], 'filename' => $m[3]],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $extra = 'data-downloadurl="'.$download_url.'" data-blob-authid="'.$m[2].'"';

            $marker_class_a   = 'dp-embed-blob-a-'.$m[2];
            $marker_class_img = 'dp-embed-blob-img-'.$m[2];

            // Add a sign code for fs-saved files
            // If the auto-code contains the '0' digit it means it was originally in the db
            // It's possible it's been moved (e.g., to fs or s3) which means the auth will have changed
            // so adding the 'sc' code makes any hard-coded links still work by using a separate sign code as auth in serve_file.php
            if (substr($m[2], -1, 1) === '0') {
                $sc_code = Util::generateStaticSecurityToken(App::getSetting('core.install_token').$m[2]);
                $aids    = App::getContainer()->getBlobStorage()->getAdapterIds();
                if (in_array('fs', $aids)) {
                    $download_url .= "?sc=$sc_code";
                }
            } else {
                $sc_code = null;
            }

            if (substr(strtolower($m[3]), -5) === '.tiff') {
                $m[1] = 'url';
            }

            if ($m[1] === 'signature_image') {
                $url = App::getRouter()->generate(
                    'serve_blob',
                    ['blob_auth_id' => $m[2], 'filename' => $m[3], 'sc' => $sc_code],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $replace = sprintf('<img src="%s" title="%s" />', $url, $m[3]);
            } elseif ($m[1] === 'image') {
                $_p = ['blob_auth_id' => $m[2], 'filename' => $m[3], 'sc' => $sc_code];
                if ($resizeInlines) {
                    $_p['s'] = 350;
                }
                $url = App::getRouter()->generate(
                    'serve_blob',
                    $_p,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $do_link = true;

                // If we arent balanced, then it means the image is within an <a>, so
                // we shouldnt link the image ourselves
                if (substr_count($before, '<a') != substr_count($before, '</a>')) {
                    $do_link = false;
                }

                if (!$do_link) {
                    $replace = sprintf(
                        '<img src="%s" title="%s" class="dragout '.$marker_class_img.'" %s/>',
                        $url,
                        $m[3],
                        $extra
                    );
                } else {
                    $replace = sprintf(
                        '<a href="%s" target="_blank" class="dp-is-image dragout '.$marker_class_a.'" %s><img src="%s" title="%s" class="'.$marker_class_img.'" /></a>',
                        $download_url,
                        $extra,
                        $url,
                        $m[3]
                    );
                }
            } else {
                $replace = sprintf(
                    '<a href="%s" target="_blank" class="dragout '.$marker_class_a.'" %s>%s</a>',
                    $download_url,
                    $extra,
                    $m[3]
                );
            }

            return $replace;
        };

        $changed = true;
        while ($changed) {
            $m       = null;
            $changed = false;

            // [attach:type:auth_id:filename]
            if (RegexUtils::safePregMatch('#\[attach:([a-zA-Z0-9\-_\.]+):([a-zA-Z0-9\-_\.]+):([a-zA-Z0-9\-_\. ]+)\]#', $message, $m)) {
                $changed = true;
                $pos     = strpos($message, $m[0]);
                $before  = substr($message, 0, $pos);
                $message = str_replace($m[0], $fn($m, $before), $message);
            }
        }

        return $message;
    }

    /**
     * @return mixed
     */
    public function convertEmbeddedImagesToInlineAttach()
    {
        $messageText = $this->convertEmbeddedImagesToInlineAttachInText($this->message);
        $this->setModelField('message', $messageText);

        return $messageText;
    }

    /**
     * @param $message_text
     *
     * @return mixed
     */
    public function convertEmbeddedImagesToInlineAttachInText($message_text)
    {
        foreach ($this->attachments as $attachment) {
            if ($attachment->is_inline) {
                $blob    = $attachment->blob;
                $replace = $blob->getEmbedCode(true);

                $regex        = '#(<img[^>]+src=")'.preg_quote($blob->getDownloadUrl(true), '#').'("[^>]*>)#i';
                $message_text = RegexUtils::safePregReplace($regex, $replace, $message_text);

                $regex        = '#<a[^>]+'.preg_quote('dp-embed-blob-a-'.$blob->getAuthId()).'[^>]*>.*?</a>#';
                $message_text = RegexUtils::safePregReplace($regex, $replace, $message_text);

                $regex        = '#<img[^>]+'.preg_quote('dp-embed-blob-img-'.$blob->getAuthId()).'[^>]>#';
                $message_text = RegexUtils::safePregReplace($regex, $replace, $message_text);
            }
        }

        // signature images - alt contains the original text
        $regex        = '#<img[^>]+class="dp-signature-image" alt="([^"]+)"[^>]*>#i';
        $message_text = RegexUtils::safePregReplace($regex, '$1', $message_text);

        return $message_text;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     *
     * @return array
     */
    public function getUsedSignatureImageBlobs()
    {
        RegexUtils::safePregMatchAll(
            '#\[attach:signature_image:([a-zA-Z0-9\-_\.]+):([a-zA-Z0-9\-_\. ]+)\]#',
            $this->message,
            $matches,
            PREG_SET_ORDER
        );
        $auth_codes = [];
        foreach ($matches as $match) {
            $auth_codes[] = $match[1];
        }

        if ($auth_codes) {
            return App::getEntityRepository('DeskPRO:Blob')->getByAuthCodes($auth_codes);
        }

        return [];
    }

    /**
     * @return string
     */
    public function getMessageText()
    {
        $message = $this->message;
        $message = Html2Text::convertHtml($message);

        return $message;
    }

    /**
     * @return string
     */
    public function getMessageFullText()
    {
        if (!$this->message_full) {
            return '';
        }

        $message = $this->message_full;
        $message = strip_tags($message);
        $message = Strings::htmlEntityDecodeUtf8($message);

        return $message;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getMessageFull()
    {
        if (!$this->message_full) {
            return '';
        }

        return $this->procInlineAttach($this->message_full);
    }

    /**
     * @return string
     */
    public function getMessagePlainHtml()
    {
        return nl2br($this->getMessageText());
    }

    /**
     * @return string
     */
    public function getOriginalMessage()
    {
        return $this->originalMessage ?: $this->message;
    }

    /**
     * @param $message
     */
    public function setMessageHtml($message)
    {
        $this->setMessage($message);
    }

    /**
     * Get a plain-text "quoted" version of the message. This is the message
     * wrapped to 75 characters and each line preceded with a >.
     *
     * @return string
     */
    public function getMessageQuote()
    {
        $message_quote = wordwrap($this->getMessageText(), 75, "\n", true);
        $message_quote = RegexUtils::safePregReplace('#^#m', '> ', $message_quote);

        return $message_quote;
    }

    /**
     * @param $message
     *
     * @return $this
     */
    public function setMessageText($message)
    {
        $this->setMessage(Strings::text2html($message));

        return $this;
    }

    /**
     * @param $message
     *
     * @return $this
     */
    public function setMessage($message)
    {
        $message = trim((string) $message);
        $this->setModelField('message', $message);

        return $this;
    }

    /**
     * @param string $originalMessage
     */
    public function setOriginalMessage($originalMessage)
    {
        $this->originalMessage = $originalMessage;
    }

    /**
     * @param TicketAttachment $attach
     *
     * @return $this
     */
    public function addAttachment(TicketAttachment $attach)
    {
        if (!$this->attachments->contains($attach)) {
            $this->attachments->add($attach);
        }

        $attach->setMessage($this);
        if ($this->ticket) {
            $this->ticket->addAttachment($attach);
        }

        return $this;
    }

    /**
     * @return TicketMessageAttribute[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $name
     *
     * @return TicketMessageAttribute|null
     */
    public function getAttribute($name)
    {
        foreach ($this->attributes as $attr) {
            if ($attr->getName() === $name) {
                return $attr;
            }
        }

        return null;
    }

    /**
     * @param TicketMessageAttribute $attr
     *
     * @return $this
     */
    public function addAttribute(TicketMessageAttribute $attr)
    {
        $this->attributes->add($attr);
        $attr->setMessage($this);

        return $this;
    }

    /**
     * @param string|TicketMessageAttribute $attr
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function removeAttribute($attr)
    {
        if (!$attr instanceof TicketMessageAttribute) {
            $attr = $this->getAttribute($attr);
            if (!$attr) {
                throw new \OutOfBoundsException();
            }
        }

        $this->attributes->removeElement($attr);

        return $this;
    }

    /**
     * @return ArrayCollection|TicketMessageVoicePhoneCall[]
     */
    public function getPhoneCallAttributes()
    {
        return $this->attributes->filter(function (TicketMessageAttribute $attribute) {
            return $attribute instanceof TicketMessageVoicePhoneCall;
        });
    }

    /**
     * @return TicketAttachment[]|PersistentCollection
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Get a collection of attachments suitable for display in an attach list (that is, excluding inlined ones).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAttachmentsList()
    {
        return $this->attachments->filter(
            function ($a) {
                return !$a->is_inline;
            }
        );
    }

    /**
     * @param TicketAttachment $attachment
     */
    public function removeAttachment(TicketAttachment $attachment)
    {
        $this->attachments->removeElement($attachment);
        $attachment->setMessage(null);

        if ($this->ticket) {
            $this->ticket->removeAttachment($attachment);
        }
    }

    /**
     * @param string $geo_country Two-letter country code or null
     *
     * @return $this
     */
    public function setGeoCountry($geo_country)
    {
        $this->setModelField('geo_country', $geo_country ?: null);

        return $this;
    }

    /**
     * Set date created.
     *
     * @param \DateTime $dateCreated
     *
     * @return $this
     */
    public function setDateCreated(\DateTime $dateCreated = null)
    {
        if (!$dateCreated) {
            $dateCreated = new \DateTime();
        }

        $this->setModelField('date_created', $dateCreated);

        return $this;
    }

    /**
     * @return string
     */
    public function getGeoCountry()
    {
        return $this->geo_country;
    }

    /**
     * Did this message originate from a gateway?
     *
     * @return bool
     */
    public function isFromGateway()
    {
        if (strpos($this->creation_system, 'gateway') === 0) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getMessageHash()
    {
        if (!$this->message_hash) {
            $this->initHashCode();
        }

        return $this->message_hash;
    }

    /**
     * Inits the hash code for this message.
     */
    public function initHashCode()
    {
        if ($this->message_hash) {
            return;
        }

        $hashes = [];

        $hashable_msg = $this->message;
        $hashable_msg = RegexUtils::safePregReplace(
            '#\[attach:([a-zA-Z0-9\-_\.]+):([a-zA-Z0-9\-_\.]+):([a-zA-Z0-9\-_\. ]+)\]#',
            '$3',
            $hashable_msg
        );

        $hashes[] = sha1($hashable_msg.($this->person ? $this->person->getEmailAddress() : 'noperson'));

        foreach ($this->attachments as $a) {
            $hashes[] = $a->blob['blob_hash'];
        }

        // Sort hashes so theyre always the same order
        sort($hashes, \SORT_STRING);

        $this->message_hash = sha1(implode('', $hashes));
        $this->_onPropertyChanged('message_hash', '', $this->message_hash);
    }

    public function resetHashCode()
    {
        $this->message_hash = null;
        $this->initHashCode();
    }

    /**
     * When a new message is added to a ticket, make sure the person has
     * their own access code ready to use.
     */
    public function initPersonAccessCode()
    {
        if ($this->person) {
            $this->ticket->addAccessCodeForPerson($this->person);
        }
    }

    public function incTicketCount()
    {
        if (!$this->ticket) {
            return;
        }

        if (!$this->is_agent_note && $this->person && $this->person->is_agent) {
            ++$this->ticket->count_agent_replies;
        } else {
            ++$this->ticket->count_user_replies;
        }
    }

    /**
     * Are there any CCed users?
     *
     * @return bool
     */
    public function numCcedParticipants()
    {
        return count($this->ticket->getUserParticipants());
    }

    /**
     * A nice and easy way to retrieve all participants in the ticket. Mainly for display.
     *
     * @return array the list of participants as an array of strings
     */
    public function getCcedParticipants()
    {
        if ($this->ticket && $this->numCcedParticipants() > 0) {
            return array_map(
                function ($p) {
                    /* @var Person $p */
                    return $p->getDisplayContact();
                },
                $this->ticket->getUserParticipants()
            );
        }

        return [];
    }

    /**
     * @return string
     */
    public function getVisitorId()
    {
        return $this->visitor_id;
    }

    /**
     * @param string $visitor_id
     */
    public function setVisitorId($visitor_id)
    {
        $this->setModelField('visitor_id', $visitor_id);
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ip_address;
    }

    /**
     * @param string $ip_address
     */
    public function setIpAddress($ip_address)
    {
        $this->setModelField('ip_address', $ip_address);
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @return TicketMessageTranslated
     */
    public function getPrimaryTranslation()
    {
        return $this->primary_translation;
    }

    /**
     * @return EmailSource
     */
    public function getEmailSource()
    {
        return $this->email_source;
    }

    /**
     * @return bool
     */
    public function hasEmailSource()
    {
        return $this->email_source !== null;
    }

    /**
     * @return bool
     */
    public function isAgentNote()
    {
        return $this->is_agent_note;
    }

    /**
     * {@inheritdoc}
     */
    public function __call($name, $arguments)
    {
        // hack to use doctrine property accessor with Basic domain __call magic
        if ($name === 'getis_agent_note') {
            return $this->isAgentNote();
        }

        return parent::__call($name, $arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $values  = parent::toApiData($primary, $deep, $visited);
        $context = new SideloadSerializationContext(['voice_phone_call']);
        $context->setInlineSideloads(true);

        foreach ($this->attributes as $attribute) {
            $serialized             = App::$container->get('serializer')->toArray(new ApiWrapper($attribute), $context);
            $values['attributes'][] = $serialized['data'];
        }

        return $values;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    /**
     * @param ClassMetadata $metadata
     *
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TicketMessage';
        $metadata->setPrimaryTable(
            [
                'name'    => 'tickets_messages',
                'indexes' => ['date_created_idx' => ['columns' => ['date_created']]],
            ]
        );
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->addLifecycleCallback('initHashCode', 'prePersist');
        $metadata->addLifecycleCallback('incTicketCount', 'prePersist');
        $metadata->addLifecycleCallback('initPersonAccessCode', 'postPersist');
        $metadata->mapField(
            [
                'fieldName'  => 'id',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'id',
                'id'         => true,
            ]
        );
        $metadata->mapField(['fieldName' => 'date_created', 'type' => 'datetime', 'columnName' => 'date_created']);
        $metadata->mapField(['fieldName' => 'is_agent_note', 'type' => 'boolean', 'columnName' => 'is_agent_note']);
        $metadata->mapField(
            ['fieldName' => 'creation_system', 'type' => 'string', 'length' => 20, 'columnName' => 'creation_system']
        );
        $metadata->mapField(
            ['fieldName' => 'ip_address', 'type' => 'string', 'length' => 30, 'columnName' => 'ip_address']
        );
        $metadata->mapField(
            ['fieldName' => 'hostname', 'type' => 'string', 'length' => 255, 'columnName' => 'hostname']
        );
        $metadata->mapField(
            [
                'fieldName'  => 'geo_country',
                'type'       => 'string',
                'length'     => 10,
                'nullable'   => true,
                'columnName' => 'geo_country',
            ]
        );
        $metadata->mapField(['fieldName' => 'email', 'type' => 'string', 'length' => 255, 'columnName' => 'email']);
        $metadata->mapField(
            ['fieldName' => 'message_hash', 'type' => 'string', 'length' => 40, 'columnName' => 'message_hash']
        );
        $metadata->mapField(['fieldName' => 'message', 'type' => 'text', 'columnName' => 'message']);
        $metadata->mapField(
            ['fieldName' => 'message_full', 'type' => 'text', 'nullable' => true, 'columnName' => 'message_full']
        );
        $metadata->mapField(
            ['fieldName' => 'message_raw', 'type' => 'text', 'nullable' => true, 'columnName' => 'message_raw']
        );
        $metadata->mapField(
            [
                'fieldName'  => 'lang_code',
                'type'       => 'string',
                'length'     => 80,
                'nullable'   => true,
                'columnName' => 'lang_code',
            ]
        );
        $metadata->mapField(['fieldName' => 'show_full_hint', 'type' => 'boolean', 'columnName' => 'show_full_hint']);

        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'ticket',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Ticket',
                'mappedBy'     => null,
                'inversedBy'   => 'messages',
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'ticket_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'person',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'person_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'email_source',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\EmailSource',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'email_source_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'primary_translation',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\TicketMessageTranslated',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'message_translated_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'visitor_id',
                'type'       => 'string',
                'length'     => 120,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'visitor_id',
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'attributes',
                'targetEntity'  => 'DeskPRO\\Bundle\\AppBundle\\Entity\\TicketMessageAttribute',
                'cascade'       => ['remove', 'persist', 'merge'],
                'mappedBy'      => 'message',
                'fetch'         => ClassMetadataInfo::FETCH_EXTRA_LAZY,
                'orphanRemoval' => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'attachments',
                'targetEntity'  => 'Application\\DeskPRO\\Entity\\TicketAttachment',
                'cascade'       => ['remove', 'persist', 'merge'],
                'mappedBy'      => 'message',
                'orphanRemoval' => true,
                'dpApi'         => true,
                'dpApiDeep'     => true,
            ]
        );

        $metadata->mapOneToMany(
            [
                'fieldName'    => 'email_message_id',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\TicketMessageEmailId',
                'mappedBy'     => 'message',
                'cascade'      => ['persist'],
            ]
        );
    }
}
