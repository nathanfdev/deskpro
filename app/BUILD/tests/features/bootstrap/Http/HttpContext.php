<?php

namespace DpBehat\Http;

use Behatch\Context\BaseContext;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class HttpContext.
 */
class HttpContext extends BaseContext
{
    private $last_file_upload_response;

    /**
     * @When I send the :arg1 file as :arg2 to :arg3
     */
    public function iSendTheFileAsVarTo($filename, $variable, $url)
    {
        /** @var \Symfony\Bundle\FrameworkBundle\Client $client */
        $client = $this->getSession()->getDriver()->getClient();
        $files  = [$variable => new UploadedFile($this->getPath($filename), $filename)];
        $client->request('POST', $url, ['Content-Type => multipart/form-data'], $files, []);

        $this->last_file_upload_response = json_decode($client->getResponse()->getContent(), true);
    }

    /**
     * @return mixed
     */
    public function getLastFileUploadResponse()
    {
        return $this->last_file_upload_response;
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    private function getPath($filename)
    {
        return realpath(__DIR__.'/../files/'.$filename);
    }

    /**
     * Click on a link and doesn't display the response in the console.
     *
     * @Given /^(?:|I )download "(?P<link>[^"]+)"$/
     */
    public function download($link)
    {
        ob_start();
        $this->getSession()->getPage()->clickLink($link);
        ob_end_clean();
    }

    /**
     * @Then /^I should see in the header "([^"]*)":"([^"]*)"$/
     */
    public function iShouldSeeInTheHeader($header, $value)
    {
        $this->assertSession()->responseHeaderContains($header, $value);
    }

    /**
     * Checks, that current page PORTAL PATH is equal to specified.
     * This is the same as Mink but we allow for locale code at the start.
     *
     * Example: Then I should be on portal portal page "/"
     * Example: And I should be on portal portal page "/bats"
     *
     * @Then /^(?:|I )should be on portal page "(?P<page>[^"]+)"$/
     */
    public function assertPortalPageAddress($page)
    {
        $input = preg_quote(ltrim($page, '/'), '/');
        $this->getMinkContext()->assertUrlRegExp("/^(\/[a-zA-Z]{2}([\\-_][a-zA-Z]{2})?)?\/$input$/");
    }
}
