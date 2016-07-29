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

namespace DeskPRO\Bundle\InstallBundle\Installer\InstallStep;

use DeskPRO\Bundle\InstallBundle\InstallSession\InstallSession;
use DpSys\SoftwareRequirements\DeskproRequirements;
use Orb\Util\Strings;
use Symfony\Component\Console\Question\Question;

class AcceptWebUrlStep extends AbstractStep
{
    /**
     * @var string
     */
    private $authcode;

    public function run()
    {
        $this->writeBigTitle('DeskPRO Web Interface');
        $this->writeln('');
        $this->writeln('We will now ensure that your web server is capable of running DeskPRO.');
        $this->writeln('');
        $this->writeln('Please enter the full URL to DeskPRO as you expect to use it. Here are a few examples:');
        $this->writeln(' > http://localhost/');
        $this->writeln(' > http://localhost:8080/');
        $this->writeln(' > http://192.168.5.40/');
        $this->writeln(' > http://internal/deskpro/');
        $this->writeln(' > https://support.example.com/');

        $env = $this->getContext()->getDpEnv();
        if ($env->getDatManager()->hasTxtFile('server_info_auth')) {
            $this->authcode = $env->getDatManager()->readTxtFile('server_info_auth');
        } else {
            $this->authcode = '';
        }

        #------------------------------
        # Get input
        #------------------------------

        $url = '';

        while (1) {
            $this->writeln('');
            if ($url) {
                $q = new Question('Enter your URL ['.$url.']> ', $url);
            } else {
                $q = new Question('Enter your URL> ');
            }
            $q->setValidator(function ($v) {
                $v = trim($v);
                $v = trim($v, '/').'/';
                $v = strtolower($v);
                if (!$v) {
                    throw new \Exception('Please enter a full URL');
                }
                if (!preg_match('#^https?://#', $v)) {
                    $v = 'http://'.$v;
                }

                return $v;
            });

            $url = $this->askQuestion($q, 'web_url');

            if ($this->validateUrl($url)) {
                break;
            }

            $this->writeln('');
            $this->writeln('Press any key when you are ready to try again...');
            fgetc(STDIN);
        }

        $this->getSession()->setWebUrl($url);
    }

    private function validateUrl($url)
    {
        if (
            $this->getSession()->getSource() == InstallSession::SOURCE_WIN_INSTALLER
            || $this->getSession()->getSource() == InstallSession::SOURCE_AUTO_INSTALLER
        ) {
            return true;
        }

        $url = rtrim($url, '/');
        $env = $this->getContext()->getDpEnv();

        #------------------------------
        # Verify its deskpro, and that its *this* deskpro
        #------------------------------

        $this->writeln('Verifying the URL is DeskPRO...');

        $code = Strings::random(10);
        $env->getDatManager()->writeTxtFile('pong_message', $code);
        $res = $this->loadUrl($url.'/index.php?__serverinfo=ping&auth='.$this->authcode);
        $env->getDatManager()->removeTxtFile('pong_message');

        if (!$res || strpos($res, 'pong') === false) {
            $this->writeln('<error>The URL you entered does not appear to be a DeskPRO URL, or the URL is not loading.</error>');

            return false;
        }

        if (!$res || strpos($res, $code) === false) {
            $this->writeln('<error>The URL you appears to be a URL for a *different* DeskPRO instance.</error>');

            return false;
        }

        $this->writeln('  > <info>OK</info>');

        #------------------------------
        # Verify the web root is ok
        #------------------------------

        $this->writeln('Verifying web root is safe...');

        foreach ([
            $url.'/app/run/test_ping.html',
            $url.'/../app/run/test_ping.html',
        ] as $test) {
            $res = $this->loadUrl($test);
            if ($res && (strpos($res, 'DESKPRO_PONG') !== false || strpos($res, 'OK') !== 0)) {
                $this->writeln('<error>It seems like you have put DeskPRO files within the web root. This is a major security issue. You must only put the www/ directory within the web root.</error>');

                return false;
            }
        }

        $this->writeln('  > <info>OK</info>');

        #------------------------------
        # Verify rewriting is ok
        #------------------------------

        $this->writeln('Verifying URL routing...');

        $check_url = $url.'/__serverinfo/url_check/path?auth='.$this->authcode;
        $res       = $this->loadUrl($check_url);
        if (!$res || strpos($res, 'DP_CHECK_SUCCESS') === false) {
            $this->writeln('<error>Your server is not routing requests properly.</error>');
            $this->writeln('');
            $this->writeln('This is the URL we were testing:');
            $this->writeln('<info>'.$check_url.'</info>');
            $this->writeln('');

            return false;
        }

        $this->writeln('  > <info>OK</info>');

        #------------------------------
        # Verify the web deps
        #------------------------------

        $env = $this->getContext()->getDpEnv();

        $this->writeln('Verifying the web server meets requirements...');

        $reqs_url = $url.'/index.php?__serverinfo=check_requirements&auth='.$this->authcode;
        if ($server_info_auth = $env->getDatManager()->readDatFile('server_info_auth', null)) {
            $reqs_url .= '&auth='.$server_info_auth['auth'];
        }

        $reqs_url_encoded = $reqs_url.'&encode-output';

        $res = $this->loadUrl($reqs_url_encoded);

        if (!$res || !$this->validateRequirements($res)) {
            $this->writeln('<error>The web server does not meet server requirements</error>');
            $this->writeln('You can view the server requirements test page here:');
            $this->writeln('<info>'.$reqs_url.'</info>');
            $this->writeln('');

            return false;
        }

        $this->writeln('  > <info>OK</info>');

        return true;
    }

    /**
     * Loads a URL.
     *
     * @param string $url
     *
     * @return string
     */
    private function loadUrl($url)
    {
        $context = stream_context_create([
            'http' => ['timeout' => 20],
            'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);

        return @file_get_contents($url, false, $context);
    }

    /**
     * Checks the payload from a web server check to see if there are failed requirements.
     *
     * @param string $res
     *
     * @return bool
     */
    private function validateRequirements($res)
    {
        if (
            $this->getSession()->getSource() == InstallSession::SOURCE_WIN_INSTALLER
            || $this->getSession()->getSource() == InstallSession::SOURCE_AUTO_INSTALLER
        ) {
            return true;
        }

        if (!$res) {
            return false;
        }

        $match = null;
        if (!preg_match('#\-{10,}BEGIN\-{10}(.*?)\-{10,}END\-{10}#s', $res, $match)) {
            return false;
        }

        $checker = @unserialize(base64_decode(trim($match[1])));

        if (!$checker || !$checker instanceof DeskproRequirements) {
            return false;
        }

        if ($checker->getFailedRequirements()) {
            return false;
        }

        return true;
    }

    public function isComplete()
    {
        return $this->getSession()->getWebUrl();
    }
}
