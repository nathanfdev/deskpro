<?php

/**
 * DeskPRO.
 */

namespace Orb\Service\Microsoft\Translate;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class Translate
{
    const OAUTH_AUTH      = 'https://api.cognitive.microsoft.com/sts/v1.0/issueToken';
    const OAUTH_SCOPE_URL = 'http://api.microsofttranslator.com';
    const API_URL         = 'http://api.microsofttranslator.com/v2/http.svc/';

    const FORMAT_WAV = 'audio/wav';
    const FORMAT_MP3 = 'audio/mp3';

    const OPT_MINSIZE    = 'MinSize';
    const OPT_MAXQUALITY = 'MaxQuality';

    const TYPE_TEXT = 'text/plain';
    const TYPE_HTML = 'text/html';

    const CAT_GENERAL = 'general';

    /**
     * @var string
     */
    protected $client_id;

    /**
     * @var string
     */
    protected $client_secret;

    /**
     * @var string
     */
    protected $access_token;

    /**
     * @var Client
     */
    protected $oauth_http_client;

    /**
     * @var Client
     */
    protected $service_http_client;

    /**
     * @param string      $client_id
     * @param string      $client_secret
     * @param null|string $access_token  Optional existing access token to use. This prevents having to make the additional request to fetch a new one
     */
    public function __construct($client_id, $client_secret, $access_token = null)
    {
        $this->client_id     = $client_id;
        $this->client_secret = $client_secret;
        $this->access_token  = $access_token;
    }

    /**
     * Set an existing access token. Set null to clear the current
     * access token so a new one is fetched.
     *
     * @param string $access_token
     */
    public function setAccessToken($access_token)
    {
        $this->access_token = $access_token;
    }

    /**
     * Get the current access token. If no access token is set, a new one will
     * be fetched.
     *
     * @return string
     */
    public function getAccessToken()
    {
        if ($this->access_token !== null) {
            return $this->access_token;
        }

        $response = $this->getOauthHttpClient()->post('', [
            RequestOptions::HEADERS => ['Ocp-Apim-Subscription-Key' => $this->client_secret],
        ]);

        $this->access_token = $response->getBody()->getContents();

        return $this->access_token;
    }

    /**
     * Translates a text string from one language to another.
     *
     * @param string|string[] $text         A string or array of strings
     * @param string|null     $from         Language to translate from, or null to auto-detect
     * @param string          $to           Language to translate to
     * @param string          $content_type Content type of the string. HTML must be well-formed
     *
     * @return string|string[]
     */
    public function translate($text, $from, $to, $content_type = self::TYPE_TEXT, $category = self::CAT_GENERAL)
    {
        $from = $this->getNearestTranslateLocale($from);
        $to   = $this->getNearestTranslateLocale($to);

        if (is_array($text)) {
            $post_body   = [];
            $post_body[] = '<TranslateArrayRequest>';
            $post_body[] = "\t<AppId/>";
            if ($from) {
                $post_body[] = "\t<From>$from</From>";
            }

            $post_body[] = "\t<Options>";
            $post_body[] = "\t\t<Category xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\">$category</Category>";
            $post_body[] = "\t\t<ContentType xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\">$content_type</ContentType>";
            $post_body[] = "\t\t<ReservedFlags xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\"/>";
            $post_body[] = "\t\t<State xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\"/>";
            $post_body[] = "\t\t<Uri xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\"/>";
            $post_body[] = "\t\t<User xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\"/>";
            $post_body[] = "\t</Options>";

            $post_body[] = "\t<Texts>";
            foreach ($text as $t) {
                $post_body[] = "\t\t<string xmlns=\"http://schemas.microsoft.com/2003/10/Serialization/Arrays\">".$this->escapeXml($t).'</string>';
            }
            $post_body[] = "\t</Texts>";
            $post_body[] = "\t<To>$to</To>";
            $post_body[] = '</TranslateArrayRequest>';
            $post_body   = implode("\n", $post_body);

            $response = $this->getServiceHttpClient()->post('TranslateArray', [
                RequestOptions::BODY => $post_body,
            ]);

            $raw_data = $this->xml($response->getBody());
            $data     = [];
            foreach ($raw_data as $l) {
                $data[] = (string) $l->TranslatedText;
            }

            return $data;
        } else {
            $response = $this->getServiceHttpClient()->get('Translate', [
                RequestOptions::QUERY => [
                    'text'        => $text,
                    'from'        => $from ?: '',
                    'to'          => $to,
                    'contentType' => $content_type,
                    'category'    => $category,
                ],
            ]);

            $raw_data = $this->xml($response->getBody());
            $lang     = (string) $raw_data;

            return $lang;
        }
    }

    /**
     * Use the Detect Method to identify the language of a selected piece of text.
     *
     * @see http://msdn.microsoft.com/en-us/library/ff512411.aspx
     * @see http://msdn.microsoft.com/en-us/library/ff512412.aspx
     *
     * @param string|string $text A string or array of strings to detect
     *
     * @throws \InvalidArgumentException
     *
     * @return string|array The lang or array of lang IDs
     */
    public function detect($text)
    {
        if (is_array($text)) {
            $response = $this->getServiceHttpClient()->post('DetectArray', [
                RequestOptions::BODY => $this->createArrayOfStringXmlBody($text),
            ]);

            $raw_data = $this->xml($response->getBody());
            $langs    = [];

            foreach ($raw_data->string as $l) {
                $langs[] = (string) $l;
            }

            return $langs;
        } else {
            $response = $this->getServiceHttpClient()->get('Detect', [
                RequestOptions::QUERY => ['text' => $text],
            ]);
            $raw_data = $this->xml($response->getBody());
            $lang     = (string) $raw_data;

            return $lang;
        }
    }

    /**
     * Returns a wave or mp3 stream of the passed-in text being spoken in the desired language.
     *
     * @see http://msdn.microsoft.com/en-us/library/ff512420.aspx
     *
     * @param string $text   A string containing a sentence or sentences of the specified language to be spoken for the wave stream. The size of the text to speak must not exceed 2000 characters
     * @param string $lang   A string representing the supported language code to speak the text in
     * @param string $format A string specifying the content-type ID
     * @param string $opt    A string specifying the quality of the audio signals
     *
     * @return string
     */
    public function speak($text, $lang, $format = self::FORMAT_WAV, $opt = self::OPT_MINSIZE)
    {
        $response = $this->getServiceHttpClient()->get('Speak', [
            RequestOptions::QUERY => [
                'text'     => $text,
                'language' => $lang,
                'format'   => $format,
                'options'  => $opt,
            ],
        ]);

        return $response->getBody();
    }

    /**
     * Obtain a list of language codes representing languages that are supported by the Translation Service.
     *
     * @see http://msdn.microsoft.com/en-us/library/ff512416.aspx
     *
     * @param bool $use_local True to use the local cache (dont do a service request)
     *
     * @return array
     */
    public function getLanguagesForTranslate($use_local = true)
    {
        if ($use_local) {
            $file = __DIR__.'/data/langs_for_translate.php';
            if (file_exists($file)) {
                return require $file;
            }
        }

        $response = $this->getServiceHttpClient()->get('GetLanguagesForTranslate');
        $raw_data = $this->xml($response->getBody());

        $data = [];
        foreach ($raw_data->string as $r) {
            $data[] = (string) $r;
        }

        return $data;
    }

    /**
     * Retrieves the languages available for speech synthesis.
     *
     * @see http://msdn.microsoft.com/en-us/library/ff512415.aspx
     *
     * @param bool $use_local True to use the local cache (dont do a service request)
     *
     * @return array
     */
    public function getLanguagesForSpeak($use_local = true)
    {
        if ($use_local) {
            $file = __DIR__.'/data/langs_for_speak.php';
            if (file_exists($file)) {
                return require $file;
            }
        }

        $response = $this->getServiceHttpClient()->get('GetLanguagesForSpeak');
        $raw_data = $this->xml($response->getBody());

        $data = [];
        foreach ($raw_data->string as $r) {
            $data[] = (string) $r;
        }

        return $data;
    }

    /**
     * Retrieves friendly names for the languages passed in as the parameter languageCodes, and localized using the passed locale language.
     *
     * This will use the local data cache unless a lang code could not be found, then a request against the service is made.
     *
     * @see http://msdn.microsoft.com/en-us/library/ff512414.aspx
     *
     * @param string[] $lang_codes An array of lang codes
     * @param string   $locale     The locale to get names for
     * @param bool     $use_local  True to use the local cache of names (dont do a service request)
     *
     * @return array
     */
    public function getLanguageNames(array $lang_codes, $locale = 'en', $use_local = true)
    {
        if ($use_local) {
            $file = __DIR__.'/data/'.$locale.'.php';
            if (file_exists($file)) {
                $all_names = require $file;

                $names = [];
                foreach ($lang_codes as $code) {
                    if (isset($all_names[$code])) {
                        $names[$code] = $all_names[$code];
                    }
                }

                if (count($names) == count($lang_codes)) {
                    return $names;
                }
            }
        }

        $body     = $this->createArrayOfStringXmlBody($lang_codes);
        $response = $this->getServiceHttpClient()->post('GetLanguageNames', [
            RequestOptions::QUERY => ['locale' => $locale],
            RequestOptions::BODY  => $body,
        ]);

        $raw_data = $this->xml($response->getBody());

        $data = [];

        $i = 0;
        foreach ($raw_data as $k => $r) {
            if (isset($lang_codes[$i])) {
                $data[$lang_codes[$i]] = (string) $r;
            }

            ++$i;
        }

        return $data;
    }

    /**
     * Just like getLanguageNames except returns just a string for a single lang code.
     *
     * @param string[] $lang_codes An array of lang codes
     * @param string   $locale     The locale to get names for
     * @param bool     $use_local  True to use the local cache of names (dont do a service request)
     *
     * @return array
     */
    public function getSingleLanguageName($lang_code, $locale = 'en', $use_local = true)
    {
        $names = $this->getLanguageNames([$lang_code], $locale, $use_local);

        return array_pop($names);
    }

    /**
     * @return Client
     */
    protected function getOauthHttpClient()
    {
        if ($this->oauth_http_client !== null) {
            return $this->oauth_http_client;
        }

        $this->oauth_http_client = new Client([
            'base_uri'             => self::OAUTH_AUTH,
            RequestOptions::VERIFY => false,
        ]);

        return $this->oauth_http_client;
    }

    /**
     * @return Client
     */
    protected function getServiceHttpClient()
    {
        if ($this->service_http_client !== null) {
            return $this->service_http_client;
        }

        $this->service_http_client = new Client([
            'base_uri'              => self::API_URL,
            RequestOptions::VERIFY  => false,
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer '.$this->getAccessToken(),
                'Content-Type'  => 'text/xml',
            ],
        ]);

        return $this->service_http_client;
    }

    /**
     * @param array $strings
     *
     * @return string
     */
    public function createArrayOfStringXmlBody(array $strings)
    {
        $body = '<ArrayOfstring xmlns="http://schemas.microsoft.com/2003/10/Serialization/Arrays" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">';
        $body .= "\n";
        foreach ($strings as $s) {
            $body .= "\t<string>".$this->escapeXml($s)."</string>\n";
        }
        $body .= '</ArrayOfstring>';

        return $body;
    }

    /**
     * @param string $str
     *
     * @return string
     */
    protected function escapeXml($str)
    {
        return str_replace(
            ['&',     '<',    '>',    '"',      "'"],
            ['&amp;', '&lt;', '&gt;', '&quot;', '&apos;'],
            $str
        );
    }

    /**
     * Checks locale to see if its supported and gets the nearest if its not. For example, 'en_US' is not supported
     * specifically but 'en' is.
     *
     * @param string $locale The locale to check
     *
     * @return string The locale replaced
     */
    public function getNearestTranslateLocale($locale)
    {
        $avail = $this->getLanguagesForTranslate();

        if (in_array($locale, $avail)) {
            return $locale;
        }

        if (strpos($locale, '_')) {
            list($top) = explode('_', $locale, 2);
            // Try again with just the first part
            return $this->getNearestTranslateLocale($top);
        }

        // Try to a case-i match
        $locale_i = strtolower($locale);
        foreach ($avail as $test) {
            if (strtolower($test) == $locale_i) {
                return $test;
            }
        }

        // No matches, return original which will probably fail
        return $locale;
    }

    /**
     * c/p from guzzle.
     *
     * @param $body
     *
     * @return \SimpleXMLElement
     */
    protected function xml($body)
    {
        $errorMessage    = null;
        $internalErrors  = libxml_use_internal_errors(true);
        $disableEntities = libxml_disable_entity_loader(true);
        libxml_clear_errors();

        try {
            $xml = new \SimpleXMLElement((string) $body ?: '<root />', LIBXML_NONET);
            if ($error = libxml_get_last_error()) {
                $errorMessage = $error->message;
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
        }

        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);
        libxml_disable_entity_loader($disableEntities);

        if ($errorMessage) {
            throw new \RuntimeException('Unable to parse response body into XML: '.$errorMessage);
        }

        return $xml;
    }
}
