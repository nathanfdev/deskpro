<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Mail\RawMessage;

use Application\EmailBundle\Mail\RawMessage\Mail\Part;
use Orb\Util\Arrays;
use Zend\Mail\Header\AbstractAddressList;
use Zend\Mail\Header\Subject;
use Zend\Mail\Storage\Exception\InvalidArgumentException;
use Zend\Mime\Decode;

/**
 * @see http://tools.ietf.org/html/rfc2822
 * @see https://tools.ietf.org/html/rfc2046
 * @see https://tools.ietf.org/html/rfc2183
 */
class Rfc2822Decoder implements RawMessageDecoderInterface
{
    /**
     * Decodes a raw RFC2822 message into parts:.
     *
     * - subject
     * - from
     * - to[]
     * - cc[]
     * - html_message
     * - text_message
     * - headers[]
     * - attachments[]
     *
     * @param resource $raw_fp
     *
     * @return RawMessage
     */
    public function createRawMessage($raw_fp)
    {
        if (!$raw = stream_get_contents($raw_fp, -1, 0)) {
            // TODO: Zend Mail Part tries to get headers from null-string
            throw new \Exception('Empty mail stream');
        }
        $message = new Part([
            'raw' => $raw,
        ]);

        $message->getHeaders()->getPluginClassLoader()->registerPlugin('bcc',      'Application\\EmailBundle\\Mail\\RawMessage\\Mail\\Header\\Bcc');
        $message->getHeaders()->getPluginClassLoader()->registerPlugin('cc',       'Application\\EmailBundle\\Mail\\RawMessage\\Mail\\Header\\Cc');
        $message->getHeaders()->getPluginClassLoader()->registerPlugin('from',     'Application\\EmailBundle\\Mail\\RawMessage\\Mail\\Header\\From');
        $message->getHeaders()->getPluginClassLoader()->registerPlugin('reply-to', 'Application\\EmailBundle\\Mail\\RawMessage\\Mail\\Header\\ReplyTo');
        $message->getHeaders()->getPluginClassLoader()->registerPlugin('to',       'Application\\EmailBundle\\Mail\\RawMessage\\Mail\\Header\\To');

        $data = [
            'from'        => $this->_readAddresses($message, 'from', true),
            'tos'         => $this->_readAddresses($message, 'to'),
            'ccs'         => $this->_readAddresses($message, 'cc'),
            'subject'     => $this->_readSubject($message),
            'headers'     => $this->_readHeaders($message),
            'text_part'   => $this->_readTextPart($message),
            'html_part'   => $this->_readHtmlPart($message),
            'attachments' => $this->_readAttachments($message),
        ];

        $raw_message = RawMessage::newFromArray($data);

        return $raw_message;
    }

    /**
     * Read a list of email addresses and returns array of array('email' => '', 'name' => '').
     *
     * @param Part   $message
     * @param string $header_name The header to read from. E.g., 'to' or 'cc'
     * @param bool   $single      True when only one address should be returned
     *
     * @return array
     */
    private function _readAddresses(Part $message, $header_name, $single = false)
    {
        try {
            $header = $message->getHeader($header_name);
        } catch (InvalidArgumentException $e) {
            return [];
        }

        if (!$header || !($header instanceof AbstractAddressList)) {
            return [];
        }

        $list = array_values(Arrays::map(function ($v) {
            return [
                'name'  => trim($v->getName(), "\"'"),
                'email' => $v->getEmail(),
            ];
        }, $header->getAddressList()));

        if ($single) {
            $list = array_shift($list);
        }

        return $list;
    }

    /**
     * Reads non-common headers from the message (i.e., headers that are not ones we handle specificaly like the subject
     * or to or cc etc).
     *
     * @param Part $message
     *
     * @return array
     */
    private function _readHeaders(Part $message)
    {
        $headers = [];

        // Headers we will ignore because we read them elsewhere
        $ignore_map = [
            'subject' => true,
            'from'    => true,
            'cc'      => true,
            'to'      => true,
        ];

        foreach ($message->getHeaders()->toArray() as $name => $value) {
            if (isset($ignore_map[strtolower($name)])) {
                continue;
            }

            if (!isset($headers[$name])) {
                $headers[$name] = [];
            }

            if (is_array($value)) {
                $headers[$name] = array_merge($headers[$name], $value);
            } else {
                $headers[$name][] = $value;
            }
        }

        return $headers;
    }

    /**
     * Reads a subject from an email message.
     *
     * @param Part $message
     *
     * @return string
     */
    private function _readSubject(Part $message)
    {
        try {
            $subject = $message->getHeader('subject');
        } catch (InvalidArgumentException $e) {
            return [];
        }

        if (!($subject instanceof Subject)) {
            return '';
        }

        return $subject->getFieldValue();
    }

    /**
     * Reads a plain-text version of the email.
     *
     * @param Part $message
     *
     * @return string
     */
    private function _readTextPart(Part $message)
    {
        return trim($this->_readBodyPart($message, 'text/plain'));
    }

    /**
     * Reads a html-text version of the email.
     *
     * @param Part $message
     *
     * @return string
     */
    private function _readHtmlPart(Part $message)
    {
        return trim($this->_readBodyPart($message, 'text/html'));
    }

    /**
     * Reads the body of a message.
     *
     * @param Part   $message
     * @param string $body_type 'text/plain' or 'text/html'
     *
     * @return string|null
     */
    private function _readBodyPart(Part $message, $body_type)
    {
        $is_plain = ($body_type == 'text/plain');

        $part = null;
        if ($message->isMultipart()) {
            $try_multi = null;
            foreach ($message as $sub_part) {
                if ($sub_part->isMultipart()) {
                    // If the sub part is also amulti,
                    // we will try to descend down the tree if no other
                    // suitable part is available
                    if (!$try_multi) {
                        $try_multi = $sub_part;
                    }
                } else {
                    // This isn't a multi-part, so just try to read from this part
                    $text = $this->_readBodyPart($sub_part, $body_type);
                    if ($text !== null) {
                        return $text;
                    }
                }
            }
            if ($try_multi) {
                return $this->_readBodyPart($try_multi, $body_type);
            }
        } else {
            $type        = $this->_getHeaderOrNull($message, 'Content-Type');
            $disposition = $this->_getHeaderOrNull($message, 'Content-Disposition');

            // Ignore attachment parts
            if ($disposition && strtok($disposition->getFieldValue(), ';') != 'inline') {
                return;
            }

            if ($is_plain) {
                // missing content-type means we assume content-type of text-plain,
                // so if its missing, its as good as being text/plain
                if (!$type || (strtok($type->getFieldValue(), ';') == $body_type)) {
                    return $this->decodeMessage($message);
                }
            } else {
                if ($type && (strtok($type->getFieldValue(), ';') == $body_type)) {
                    return $this->decodeMessage($message);
                }
            }
        }

        return;
    }

    /**
     * @param Part $message
     * @param $header_name
     *
     * @return array|\ArrayIterator|null|string|\Zend\Mail\Header\HeaderInterface
     */
    private function _getHeaderOrNull(Part $message, $header_name)
    {
        try {
            return $message->getHeader($header_name);
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * Reads attachments from the email.
     *
     * @param Part $message
     *
     * @return string
     */
    private function _readAttachments(Part $message)
    {
        $attachments = [];

        $part = null;
        if ($message->isMultipart()) {
            foreach ($message as $sub_part) {
                $attachments = array_merge($attachments, $this->_readAttachments($sub_part));
            }
        } else {
            $type         = $this->_getHeaderOrNull($message, 'Content-Type');
            $type_split   = $type ? Decode::splitHeaderField($type->getFieldValue()) : null;
            $content_type = $type_split ? $type_split[0] : 'application/octet-stream';

            $disposition       = $this->_getHeaderOrNull($message, 'Content-Disposition');
            $disposition_split = $disposition ? Decode::splitHeaderField($disposition->getFieldValue()) : null;
            $is_attach_disp    = $disposition_split ? $disposition_split[0] == 'attachment' : false;

            $is_attach = false;
            if (!$type && !$is_attach_disp) {
                $is_attach = false;
            } elseif ($content_type == 'text/plain' || $content_type == 'text/html') {
                if ($is_attach_disp) {
                    $is_attach = true;
                }
            } else {
                $is_attach = true;
            }

            if ($is_attach) {
                $filename = 'file';
                if (!empty($type_split['filename'])) {
                    $filename = $type_split['filename'];
                } elseif (!empty($disposition_split['filename'])) {
                    $filename = $disposition_split['filename'];
                }

                $content_id_header = $this->_getHeaderOrNull($message, 'Content-ID');
                $content_id        = $content_id_header ? Decode::splitHeaderField($content_id_header->getFieldValue(), 0) : null;

                $data = $this->decodeMessage($message);

                $a = [
                    'filename' => $filename,
                    'cid'      => $content_id,
                    'bin_data' => $data,
                    'type'     => $content_type,
                    'crc32'    => sprintf('%x', crc32($data)),
                ];

                $attachments[] = $a;
            }
        }

        return $attachments;
    }

    protected function decodeMessage(Part $message)
    {
        $enc      = $this->_getHeaderOrNull($message, 'Content-Transfer-Encoding');
        $enc_type = $enc ? Decode::splitHeaderField($enc->getFieldValue(), 0) : 'binary';
        $charset  = 'utf8';
        if ($type = $this->_getHeaderOrNull($message, 'Content-Type')) {
            $charset = $type->getParameter('charset');
        }

        $data = $message->getContent();
        switch (strtolower($enc_type)) {
            case 'quoted-printable':
            case 'base64':
                $data = self::decodeString($data, $enc_type);
                break;
        }

        if ($charset && $charset !== 'utf8') {
            $data = mb_convert_encoding($data, 'utf8', $charset);
        }

        return $data;
    }

    /**
     * @param string $string
     * @param string $enc_type The way the string is encoded: quoted-printable, base64, 7bit, 8bit
     *
     * @return string
     */
    public static function decodeString($string, $enc_type)
    {
        switch (strtolower($enc_type)) {
            case 'quoted-printable':
                $string = quoted_printable_decode($string);
                break;
            case 'base64':
                $string = base64_decode($string);
                break;
            case '8bit':
            case '7bit':
            case 'binary':
                // nothing
                break;
            default:
                throw new \InvalidArgumentException();
        }

        return $string;
    }
}
