<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DpBehat\Portal;

use Guzzle\Http\Message\Response;
use Sanpi\Behatch\Context\BaseContext;

class FileContext extends BaseContext
{
    /**
     * @var Response
     */
    private $response;

    /**
     * @When /^I try to download "([^"]*)"$/
     */
    public function iTryToDownload($link)
    {
        $elt = $this->getSession()->getPage()->findLink($link);
        if ($elt) {
            $value  = $elt->getAttribute('href');
            $driver = $this->getSession()->getDriver();
            $jar    = new \Guzzle\Plugin\Cookie\CookieJar\ArrayCookieJar();
            if ($driver instanceof \Behat\Mink\Driver\Selenium2Driver) {
                $ds      = $driver->getWebDriverSession();
                $cookies = $ds->getAllCookies();
                for ($i = 0; $i < count($cookies); ++$i) {
                    $cookie = new \Guzzle\Plugin\Cookie\Cookie();
                    $cookie->setName($cookies[$i]['name']);
                    $cookie->setValue($cookies[$i]['value']);
                    $cookie->setDomain($cookies[$i]['domain']);
                    $jar->add($cookie);
                }
            } elseif ($driver instanceof \Behat\Symfony2Extension\Driver\KernelDriver) {
                $cookies = $driver->getClient()->getCookieJar()->all();
                for ($i = 0; $i < count($cookies); ++$i) {
                    $cookie = new \Guzzle\Plugin\Cookie\Cookie();
                    $cookie->setName($cookies[$i]->getName());
                    $cookie->setValue($cookies[$i]->getValue());
                    $cookie->setDomain($cookies[$i]->getDomain());
                    $jar->add($cookie);
                }
            } else {
                echo get_class(($driver));
                throw new \InvalidArgumentException('Not Supported Driver');
            }

            $client = new \Guzzle\Http\Client($this->getSession()->getCurrentUrl());
            $client->addSubscriber(new \Guzzle\Plugin\Cookie\CookiePlugin($jar));

            $request        = $client->get($value);
            $this->response = $request->send();
        } else {
            throw new \InvalidArgumentException(sprintf('Could not evaluate: "%s"', $link));
        }

        return;
        echo $this->getSession()->getCurrentUrl()."\n";
        $client = new \Guzzle\Http\Client($this->getSession()->getCurrentUrl());

        echo $this->locatePath($url)."\n";
        $request        = $client->get($this->locatePath($url));
        $this->response = $request->send();
    }

    /**
     * @Then /^I should see response status code "([^"]*)"$/
     */
    public function iShouldSeeResponseStatusCode($statusCode)
    {
        $responseStatusCode = $this->response->getStatusCode();

        if (!$responseStatusCode == intval($statusCode)) {
            throw new \Exception(sprintf('Did not see response status code %s, but %s.', $statusCode, $responseStatusCode));
        }
    }

    /**
     * @Then /^I should see in the header "([^"]*)":"([^"]*)"$/
     */
    public function iShouldSeeInTheHeader($header, $value)
    {
        $headers = $this->response->getHeaders();
        if ($headers->get($header) != $value) {
            throw new \Exception(sprintf('Did not see %s with value %s.', $header, $value));
        }
    }
}
