<?php

namespace Orb\Service\Microsoft\Translate;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\RequestOptions;
use Orb\Service\Microsoft\Translate\Exceptions\EntireTextTooLongException;
use Orb\Service\Microsoft\Translate\Exceptions\TextValueTooLongException;
use Orb\Util\Arrays;
use Psr\Http\Message\RequestInterface;

/**
 * Class Translate
 *
 * @package Orb\Service\Microsoft\Translate
 * @url https://docs.microsoft.com/en-us/azure/cognitive-services/translator/reference/v3-0-reference
 */
class Translate
{
    const OAUTH_AUTH        = 'https://api.cognitive.microsoft.com/sts/v1.0/issueToken';
    const OAUTH_SCOPE_URL   = 'http://api.microsofttranslator.com';
    const API_URL           = 'https://api.cognitive.microsofttranslator.com';
    const API_VERSION_QUERY = 'api-version=3.0';

    const FORMAT_WAV = 'audio/wav';
    const FORMAT_MP3 = 'audio/mp3';

    const OPT_MINSIZE    = 'MinSize';
    const OPT_MAXQUALITY = 'MaxQuality';

    const TYPE_TEXT = 'plain';
    const TYPE_HTML = 'html';

    const CAT_GENERAL = 'general';

    /**
     * @url https://docs.microsoft.com/en-us/azure/cognitive-services/translator/reference/v3-0-translate?tabs=curl#request-body
     *
     * Single request can't have more then 100 elements in texts array.
     * The text value of an array element cannot exceed 5,000 characters including spaces.
     */
    const LIMIT_TRANS_TEXT_ELEMENTS = 100;
    const LIMIT_TRANS_TEXT_LENGTH   = 5000;

    /**
     * @url https://docs.microsoft.com/en-us/azure/cognitive-services/translator/reference/v3-0-detect?tabs=curl
     *
     * Single request can't have more then 100 elements in texts array.
     * The text value of an array element cannot exceed 10,000 characters including spaces.
     * The entire text included in the request cannot exceed 50,000 characters including spaces.
     */
    const LIMIT_DETECT_TEXT_ELEMENTS = 100;
    const LIMIT_DETECT_TEXT_LENGTH   = 10000;
    const LIMIT_DETECT_ENTIRE_TEXT   = 50000;

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
     * @see https://docs.microsoft.com/en-us/azure/cognitive-services/translator/reference/v3-0-translate?tabs=curl
     *
     * @param string|string[] $text     A string or array of strings
     * @param string|null     $from     Language to translate from, or null to auto-detect
     * @param string          $to       Language to translate to
     * @param string          $textType Content type of the string. HTML must be well-formed
     * @param string          $category A string specifying the category (domain) of the translation.
     *
     * @return string|string[]
     * @throws TextValueTooLongException
     */
    public function translate($text, $from, $to, $textType = self::TYPE_TEXT, $category = self::CAT_GENERAL)
    {
        $from = $this->getNearestTranslateLocale($from);
        $to   = $this->getNearestTranslateLocale($to);

        $isTextArray = is_array($text);
        $wrappedText = $isTextArray ? $text : [$text];

        $result = [];
        $page   = 1;

        while (!empty($textChunk = Arrays::getPageChunk($wrappedText, $page, self::LIMIT_TRANS_TEXT_ELEMENTS))) {
            $page++;

            $requestBody = [];
            foreach ($textChunk as $textItem) {
                if (strlen($textItem) > self::LIMIT_TRANS_TEXT_LENGTH) {
                    throw new TextValueTooLongException(self::LIMIT_TRANS_TEXT_LENGTH);
                }
                $requestBody[] = ['Text' => $textItem];
            }

            $response = $this->getServiceHttpClient()->post('translate', [
                RequestOptions::QUERY => [
                    'text'        => $text,
                    'from'        => $from ?: '',
                    'to'          => $to,
                    'textType'    => $textType,
                    'category'    => $category,
                ],
                RequestOptions::JSON  => $requestBody,
            ]);
            $data = json_decode($response->getBody(), true);

            foreach ($data as $translation) {
                $result[] = $translation['translations'][0]['text'];
            }
        }

        return $isTextArray ? $result : array_shift($result);
    }

    /**
     * Use the Detect Method to identify the language of a selected piece of text.
     *
     * @see https://docs.microsoft.com/en-us/azure/cognitive-services/translator/reference/v3-0-detect?tabs=curl
     *
     * @param string|array $text A string or array of strings to detect
     *
     * @throws \Exception
     *
     * @return string|array The lang or array of lang IDs
     */
    public function detect($text)
    {
        $isTextArray = is_array($text);
        $wrappedText = $isTextArray ? $text : [$text];

        $result = [];
        $page   = 1;

        while (!empty($textChunk = Arrays::getPageChunk($wrappedText, $page, self::LIMIT_DETECT_TEXT_ELEMENTS))) {
            $page++;

            $requestBody = [];
            $totalLength = 0;
            foreach ($textChunk as $textItem) {
                $stringLength = strlen($textItem);
                if ($stringLength > self::LIMIT_DETECT_TEXT_LENGTH) {
                    throw new TextValueTooLongException(self::LIMIT_DETECT_TEXT_LENGTH);
                }

                $totalLength += $stringLength;
                if ($totalLength > self::LIMIT_DETECT_ENTIRE_TEXT) {
                    throw new EntireTextTooLongException(self::LIMIT_DETECT_ENTIRE_TEXT);
                }

                $requestBody[] = ['Text' => $textItem];
            }

            $response = $this->getServiceHttpClient()->post('detect', [
                RequestOptions::JSON => $requestBody,
            ]);
            $data = json_decode($response->getBody(), true);

            foreach ($data as $detection) {
                $result[] = $detection['language'];
            }
        }

        return $isTextArray ? $result : array_shift($result);
    }

    /**
     * Obtain a list of language codes representing languages that are supported by the Translation Service.
     *
     * @see https://docs.microsoft.com/en-us/azure/cognitive-services/translator/reference/v3-0-languages?tabs=curl
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

        $response = $this->getServiceHttpClient()->get('languages', [
            RequestOptions::QUERY => ['scope' => 'translation'],
        ]);
        $data = json_decode($response->getBody(), true);

        return array_keys($data['translation']);
    }

    /**
     * Retrieves friendly names for the languages passed in as the parameter languageCodes, and localized using the passed locale language.
     *
     * This will use the local data cache unless a lang code could not be found, then a request against the service is made.
     *
     * @see https://docs.microsoft.com/en-us/azure/cognitive-services/translator/reference/v3-0-languages?tabs=curl
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

                if (count($names) === count($lang_codes)) {
                    return $names;
                }
            }
        }

        $response = $this->getServiceHttpClient()->get('languages', [
            RequestOptions::HEADERS => [
                'Accept-Language' => $locale,
            ],
            RequestOptions::QUERY   => ['scope' => 'translation'],
        ]);
        $data = json_decode($response->getBody(), true);

        $result = [];
        foreach ($data['translation'] as $code => $language) {
            if (in_array($code, $lang_codes, true)) {
                $result[$code] = $language['name'];
            }
        }

        return $result;
    }

    /**
     * Just like getLanguageNames except returns just a string for a single lang code.
     *
     * @param string   $lang_code An array of lang codes
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

        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());

        $stack->push(Middleware::mapRequest(function (RequestInterface $request) {
            $uri = $request->getUri();
            $query = $uri->getQuery();

            $query .= empty($query) ? '' : '&';
            $query .= self::API_VERSION_QUERY;

            return $request->withUri($uri->withQuery($query));
        }));

        $this->service_http_client = new Client([
            'base_uri'              => self::API_URL,
            'handler'               => $stack,
            RequestOptions::VERIFY  => false,
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer '.$this->getAccessToken(),
                'Content-Type'  => 'application/json',
            ],
        ]);

        return $this->service_http_client;
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

        if (in_array($locale, $avail, true)) {
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
}
