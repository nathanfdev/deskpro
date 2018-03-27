<?php

namespace DpBehat\Api;

use Application\DeskPRO\Entity\Session;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\Goutte\Client;
use Behat\Mink\Exception\ExpectationException;
use Behatch\Context\BaseContext;
use DpBehat\Data\DataContext;
use Orb\Util\Util;
use Symfony\Component\HttpFoundation\Request;

class RestContext extends BaseContext
{
    protected $server_params = [];

    /**
     * Add an header element in a request.
     *
     * @Then I add :name header equal to :value
     */
    public function iAddHeaderEqualTo($name, $value, $isJson = false)
    {
        // we need to pass them as $_SERVER...
        $name = str_replace('-', '_', strtoupper(trim($name)));
        $name = 'HTTP_'.$name;

        if (!$isJson) {
            $value = DataContext::replace($value);
        }

        $this->server_params[$name] = trim($value);
    }

    /**
     * @Then I add Authorization header of my Api Key
     */
    public function iAddAuthorizationHeader()
    {
        $key = DataContext::getReference('apiKey');
        $this->iAddHeaderEqualTo('Authorization', "key {$key->getId()}:{$key->code}");
    }

    /**
     * Add a cookie.
     *
     * @Then I add cookie named :name equal to :value
     */
    public function iAddACookie($name, $value)
    {
        $this->getSession()->getDriver()->setCookie($name, DataContext::replace($value));
    }

    /**
     * Add a session cookie.
     *
     * @Then I add session cookie named :name for session :session
     */
    public function iAddASessionCookie($name, $session)
    {
        /** @var Session $session */
        $session   = DataContext::getReference($session);
        $sessionId = Util::baseEncode($session->getId(), 'base36');
        $this->getSession()->getDriver()->setCookie($name, implode('-', [$sessionId, $session->getAuth()]));
    }

    /**
     * Sends a HTTP request.
     *
     * @Given I send a :method request to :url
     */
    public function iSendARequestTo($method, $url)
    {
        $url = DataContext::replace($url);
        DataContext::setPlaceholder('lastRequestUrl', $url);

        /** @var \Symfony\Bundle\FrameworkBundle\Client $client */
        $client = $this->getSession()->getDriver()->getClient();

        // intercept redirection
        $client->followRedirects(false);

        $client->request($method, $this->locatePath($url), [], [], $this->server_params);
        $client->followRedirects(true);

        $page = $this->getSession()->getPage();
        if (strtoupper($method) === 'POST') {
            $this->saveLastCreatedId($page->getContent());
        }

        return $page;
    }

    /**
     * Sends a HTTP request with a some parameters.
     *
     * @Given I send a :method request to :url with parameters:
     */
    public function iSendARequestToWithParameters($method, $url, TableNode $datas)
    {
        $url = DataContext::replace($url);
        DataContext::setPlaceholder('lastRequestUrl', $url);

        $client = $this->getSession()->getDriver()->getClient();

        // intercept redirection
        $client->followRedirects(false);

        $parameters = [];
        foreach ($datas->getHash() as $row) {
            if (!isset($row['key']) || !isset($row['value'])) {
                throw new \Exception("You must provide a 'key' and 'value' column in your table node.");
            }
            $row['value'] = DataContext::replace($row['value']);
            if (is_string($row['value']) && substr($row['value'], 0, 1) == '@') {
                $row['value'] = '@'.rtrim($this->getMinkParameter('files_path'), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.substr($row['value'], 1);
            }

            $parameters[] = sprintf('%s=%s', $row['key'], $row['value']);
        }

        parse_str(implode('&', $parameters), $parameters);

        $client->request($method, $this->locatePath($url), $parameters, [], $this->server_params);
        $client->followRedirects(true);

        $page = $this->getSession()->getPage();
        if (strtoupper($method) === 'POST') {
            $this->saveLastCreatedId($page->getContent());
        }

        return $page;
    }

    /**
     * Sends a HTTP request with a body.
     *
     * @Given I send a :method request to :url with body:
     */
    public function iSendARequestToWithBody($method, $url, PyStringNode $body)
    {
        $url = DataContext::replace($url);
        DataContext::setPlaceholder('lastRequestUrl', $url);

        $client = $this->getSession()->getDriver()->getClient();

        // intercept redirection
        $client->followRedirects(false);

        $content = DataContext::replace($body->getRaw(), true);
        $client->request($method, $this->locatePath($url), [], [], $this->server_params, $content);
        $client->followRedirects(true);

        $page = $this->getSession()->getPage();
        if (strtoupper($method) === 'POST') {
            $this->saveLastCreatedId($page->getContent());
        }

        return $page;
    }

    /**
     * Sends a HTTP request with a file as body body.
     *
     * @Given I send a :method request to :url with content type :contentType and file :filePath as body
     *
     * @param $method
     * @param $url
     * @param $contentType
     * @param $filePath
     */
    public function iSendARequestToWithFileAsBody($method, $url, $contentType, $filePath)
    {
        $url      = DataContext::replace($url);
        $filePath = DataContext::replace($filePath);

        /** @var \Symfony\Bundle\FrameworkBundle\Client $client */
        $client = $this->getSession()->getDriver()->getClient();

        // intercept redirection
        $client->followRedirects(false);

        $serverParams = array_merge($this->server_params, ['CONTENT_TYPE' => $contentType]);
        $client->request($method, $this->locatePath($url), [], [], $serverParams, file_get_contents($filePath));

        $page = $this->getSession()->getPage();
        if (strtoupper($method) === 'POST') {
            $this->saveLastCreatedId($page->getContent());
        }

        return $page;
    }

    /**
     * Sends a HTTP request with a body.
     *
     * @Given I send a :method request to :url with a json body:
     */
    public function iSendARequestToWithJsonBody($method, $url, PyStringNode $body)
    {
        $url = DataContext::replace($url);
        DataContext::setPlaceholder('lastRequestUrl', $url);

        $client = $this->getSession()->getDriver()->getClient();

        // intercept redirection
        $client->followRedirects(false);

        $content = DataContext::replace($body->getRaw(), true);
        $encodedContent = json_encode(json_decode($content));

        $client->request($method, $this->locatePath($url), [], [], $this->server_params, $encodedContent);
        $client->followRedirects(true);

        $page = $this->getSession()->getPage();
        if (strtoupper($method) === 'POST') {
            $this->saveLastCreatedId($page->getContent());
        }

        return $page;
    }

    /**
     * Saves the last created id as a different alias so it can be reused when multiple requests fire in same scenario
     *
     * @Given I save the last created id as :alias
     */
    public function iSaveLastCreatedIdAs($alias)
    {
        $placeholder = DataContext::getPlaceholder('lastCreatedId', true);
        DataContext::setPlaceholder($alias, $placeholder);
    }

    /**
     * Checks, whether the response content is equal to given text.
     *
     * @Then the response should be equal to
     */
    public function theResponseShouldBeEqualTo(PyStringNode $expected)
    {
        $expected = str_replace('\\"', '"', $expected);
        $actual   = $this->getSession()->getPage()->getContent();
        $message  = sprintf('The string "%s" is not equal to the response of the current page', $expected);
        $this->assertEquals($expected, $actual, $message);
    }

    /**
     * Checks, whether the response content is null or empty string.
     *
     * @Then the response should be empty
     */
    public function theResponseShouldBeEmpty()
    {
        $actual  = $this->getSession()->getPage()->getContent();
        $message = 'The response of the current page is not empty';
        $this->assertTrue(null === $actual || '' === $actual, $message);
    }

    /**
     * Checks, whether the response content contains the text.
     *
     * @Then the JSON response should contain :text
     *
     * @param string $text
     */
    public function theResponseShouldContain($text)
    {
        $actual = $this->getSession()->getPage()->getContent();

        $this->assertContains($text, (string) $actual);
    }

    /**
     * Checks, whether the header name is equal to given text.
     *
     * @Then the header :name should be equal to :value
     */
    public function theHeaderShouldBeEqualTo($name, $value)
    {
        $value  = DataContext::replace($value);
        $actual = $this->getHttpHeader($name);
        $this->assertEquals(strtolower($value), strtolower($actual),
            sprintf('The header "%s" is equal to "%s"', $name, $actual)
        );
    }

    /**
     * Checks, whether the header matches a regular expression.
     *
     * @Then the header :name should match :regex
     */
    public function theHeaderShouldMatch($name, $regex)
    {
        $actual  = $this->getHttpHeader($name);
        $message = sprintf('Header "%s" value "%s" does not match the regex "%s".', $name, $actual, $regex);
        $this->assertTrue((bool) preg_match($regex, $actual), $message);
    }

    /**
     * Checks, whether the header name contains the given text.
     *
     * @Then the header :name should contain :value
     */
    public function theHeaderShouldBeContains($name, $value)
    {
        $this->assertContains($value, $this->getHttpHeader($name),
            sprintf('The header "%s" doesn\'t contain "%s"', $name, $value)
        );
    }

    /**
     * Checks, whether the header name doesn't contain the given text.
     *
     * @Then the header :name should not contain :value
     */
    public function theHeaderShouldNotContain($name, $value)
    {
        $this->assertNotContains($value, $this->getHttpHeader($name),
            sprintf('The header "%s" contains "%s"', $name, $value)
        );
    }

    /**
     * Checks, whether the header not exist.
     *
     * @Then the header :name should not exist
     */
    public function theHeaderShouldNotExist($name)
    {
        try {
            $this->getHttpHeader($name);
            $message = sprintf('The header "%s" exist', $name);
            throw new ExpectationException($message, $this->getSession());
        } catch (\OutOfBoundsException $e) {
        }
    }

    /**
     * Checks, that the response header expire is in the future.
     *
     * @Then the response should expire in the future
     */
    public function theResponseShouldExpireInTheFuture()
    {
        $date    = new \DateTime($this->getHttpHeader('Date'));
        $expires = new \DateTime($this->getHttpHeader('Expires'));

        $this->assertSame(1, $expires->diff($date)->invert,
            sprintf(sprintf('The response doesn\'t expire in the future (%s)', $expires->format(DATE_ATOM)))
        );
    }

    /**
     * @Then the response should be encoded in :encoding
     */
    public function theResponseShouldBeEncodedIn($encoding)
    {
        $content = $this->getSession()->getPage()->getContent();
        if (!mb_check_encoding($content, $encoding)) {
            throw new \Exception("The response is not encoded in $encoding");
        }

        $this->theHeaderShouldBeContains('Content-Type', "charset=$encoding");
    }

    /**
     * @Then print last response headers
     */
    public function printLastResponseHeaders()
    {
        $text    = '';
        $headers = $this->getHttpHeaders();

        foreach ($headers as $name => $value) {
            $text .= $name.': '.$this->getHttpHeader($name)."\n";
        }
        echo $text;
    }

    /**
     * @Then print last response body
     */
    public function printLastResponseBody()
    {
        $page = $this->getSession()->getPage();
        if ($page) {
            echo $page->getContent();
        }
    }

    /**
     * @Then print the corresponding curl command
     */
    public function printTheCorrespondingCurlCommand()
    {
        /** @var Request $request */
        $request = $this->getSession()->getDriver()->getClient()->getRequest();

        $method = $request->getMethod();
        $url    = $request->getUri();

        $headers = '';
        foreach ($request->headers->all() as $name => $value) {
            $headerValue = is_array($value) ? implode(' ', $value) : $value;
            $headers .= " -H '$name: $headerValue'";
        }

        $dataBinary = '';
        $content = $request->getContent();
        if (! empty($content)) {
            $content = str_replace("\n", "\\\n", $content);
            $dataBinary = "--data-binary '$content'";
        }

        echo "curl -X $method $headers $dataBinary '$url'";
    }

    /**
     * @Then I can load and save object via :url
     *
     * @param string $url
     */
    public function canLoadAndSaveObject($url)
    {
        $url = DataContext::replace($url);

        /** @var Client $client */
        $client = $this->getSession()->getDriver()->getClient();
        $client->request('GET', $this->locatePath($url), [], [], $this->server_params);

        $data    = json_decode($client->getResponse()->getContent(), true);
        $data    = $data['data'];
        $content = json_encode($data);

        $client->request('PUT', $this->locatePath($url), [], [], $this->server_params, $content);

        $response = $client->getResponse();
        $this->assertEquals(204, $response->getStatusCode());
    }

    private function getHttpHeader($name)
    {
        $name   = strtolower($name);
        $header = $this->getHttpHeaders();

        if (isset($header[$name])) {
            if (is_array($header[$name])) {
                $value = implode(', ', $header[$name]);
            } else {
                $value = $header[$name];
            }
        } else {
            throw new \OutOfBoundsException(
                sprintf('The header "%s" doesn\'t exist', $name)
            );
        }

        return $value;
    }

    private function getHttpHeaders()
    {
        return array_change_key_case(
            $this->getSession()->getResponseHeaders(),
            CASE_LOWER
        );
    }

    /**
     * @param string $content
     *
     * @throws \Exception
     *
     * @return mixed
     */
    private function saveLastCreatedId($content)
    {
        $data = json_decode($content, true);
        if (!$data || !is_array($data)) {
            return;
        }
        if (!array_key_exists('data', $data)) {
            return;
        }
        if (!array_key_exists('id', $data['data'])) {
            return;
        }

        DataContext::setPlaceholder('lastCreatedId', $data['data']['id']);
    }
}
