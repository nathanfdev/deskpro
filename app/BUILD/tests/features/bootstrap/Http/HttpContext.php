<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
}
