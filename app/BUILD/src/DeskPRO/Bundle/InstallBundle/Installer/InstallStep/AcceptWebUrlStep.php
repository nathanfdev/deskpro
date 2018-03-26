<?php

namespace DeskPRO\Bundle\InstallBundle\Installer\InstallStep;

use DeskPRO\Bundle\InstallBundle\InstallSession\InstallSession;
use DeskPRO\Component\Util\EnvUtils;
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

        //------------------------------
        // Get input
        //------------------------------

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
                $this->writeln('');
                $this->writeln('For help troubleshooting this, refer to this page:');
                $this->writeln('https://support.deskpro.com/kb/articles/558');
                break;
            }

            $this->writeln('');
            $this->writeln('Press any key when you are ready to try again...');
            fgetc(STDIN);
        }

        $this->getSession()->setWebUrl($url);
    }

    /**
     * @param $url
     *
     * @return bool
     */
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

        //------------------------------
        // Verify its deskpro, and that its *this* deskpro
        //------------------------------

        $this->writeln('Verifying the URL is DeskPRO...');

        $code = Strings::random(10);
        $env->getDatManager()->writeTxtFile('pong_message', $code);
        $res = $this->loadUrl($url.'/index.php?__serverinfo=ping&auth='.$this->authcode);
        $env->getDatManager()->removeTxtFile('pong_message');

        if (!$res['result']) {
            $this->writeln('<error>The URL you entered is not loading.</error>');

            return false;
        }

        if (strpos($res['result'], 'pong') === false) {
            $this->writeCurlInfo($res);
            $this->writeln('<error>The URL you entered does not point to DeskPRO.</error>');
            $this->writeln('Refer to the request output above for the result of the test.');

            return false;
        }

        if (strpos($res['result'], $code) === false) {
            $this->writeCurlInfo($res);
            $this->writeln('<error>The URL you entered appears to be a URL for a *different* DeskPRO instance.</error>');
            $this->writeln('Refer to the request output above for the result of the test.');

            return false;
        }

        $this->writeln('  > <info>OK</info>');

        //------------------------------
        // Verify rewriting is ok
        //------------------------------

        $this->writeln('Verifying URL routing...');

        $check_url = $url.'/__serverinfo/url_check/path?auth='.$this->authcode;
        $res       = $this->loadUrl($check_url);
        if (!$res['result'] || strpos($res['result'], 'DP_CHECK_SUCCESS') === false) {
            $this->writeCurlInfo($res);

            $this->writeln('<error>Your server is not routing requests properly.</error>');
            $this->writeln('Refer to the request output above for the result of the test.');

            $this->writeln('');
            $this->writeln('This is the URL we were testing:');
            $this->writeln('<info>'.$check_url.'</info>');
            $this->writeln('');

            return false;
        }

        $this->writeln('  > <info>OK</info>');

        //------------------------------
        // Verify the web deps
        //------------------------------

        $env = $this->getContext()->getDpEnv();

        $this->writeln('Verifying the web server meets requirements...');

        $reqs_url = $url.'/index.php?__serverinfo=check_requirements&auth='.$this->authcode;
        if ($server_info_auth = $env->getDatManager()->readDatFile('server_info_auth', null)) {
            $reqs_url .= '&auth='.$server_info_auth['auth'];
        }

        $reqs_url_encoded = $reqs_url.'&encode-output';

        $res = $this->loadUrl($reqs_url_encoded);

        if (!$res['result'] || !$this->validateRequirements($res['result'])) {
            $this->writeln('<error>The web server does not meet server requirements</error>');
            $this->writeln('You can view the server requirements test page here:');
            $this->writeln('<info>'.$reqs_url.'</info>');
            $this->writeln('');

            return false;
        }

        $this->writeln('  > <info>OK</info>');

        //------------------------------
        // Verify the web root is ok
        //------------------------------

        $this->writeln('Verifying web root is safe...');

        foreach ([
            $url.'/app/run/test_ping.html',
            $url.'/../app/run/test_ping.html',
        ] as $test) {
            $res = $this->loadUrl($test);
            if ($res['result'] && strpos($res['result'], 'DESKPRO_PONG') !== false) {
                $this->writeln('<error>It seems like you have put DeskPRO files within the web root. This is a major security issue. You must only put the www/ directory within the web root.</error>');

                return false;
            }
        }

        $this->writeln('  > <info>OK</info>');

        //------------------------------
        // Verify HTTP verbs
        //------------------------------

        $this->writeln('Verifying the web server responds to GET, POST, DELETE, PUT verbs...');

        foreach (['GET', 'POST', 'PUT', 'DELETE'] as $method) {
            $res = $this->loadUrl($url.'/index.php?__serverinfo=check_http_methods&auth='.$this->authcode,
            $method);
            if (strpos($res['result'], "HTTP_METHOD_{$method}") === false) {
                $this->writeCurlInfo($res);

                $this->writeln('');

                $this->writeln('<error>The web server did not respond properly to a '.$method.' request</error>');
                $this->writeln('This usually means your server is blocking these HTTP verbs. You must edit your server configuration to allow them.');
                $this->writeln('Refer to the request output above for the result of the test.');

                if (EnvUtils::isWindows()) {
                    $this->writeln('This can be a common problem when using IIS on Windows. Refer to our knowledgebase article for instructions on how to fix it:');
                    $this->writeln('https://support.deskpro.com/kb/articles/210');
                }

                $this->writeln('');

                return false;
            }
            $this->writeln("  > <info>$method OK</info>");
        }

        return true;
    }

    /**
     * Loads a URL.
     *
     * @param string $url
     * @param string $method
     *
     * @return string
     */
    private function loadUrl($url, $method = 'GET')
    {
        $resource = curl_init($url);
        curl_setopt_array(
            $resource,
            [
                CURLOPT_HEADER         => true,
                CURLINFO_HEADER_OUT    => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 20,
                CURLOPT_SSL_VERIFYPEER => false,
            ]
        );

        if ($method === 'POST' || $method === 'PUT') {
            curl_setopt($resource, CURLOPT_HTTPHEADER, [
                'Content-Length: 0',
            ]);
        }

        curl_setopt($resource, CURLOPT_CUSTOMREQUEST, strtoupper($method));

        $result = curl_exec(($resource));
        $info   = curl_getinfo($resource);

        return [
            'method'  => $method,
            'url'     => $url,
            'result'  => $result ? substr($result, $info['header_size']) : false,
            'headers' => $result ? trim(substr($result, 0, $info['header_size'])) : false,
            'info'    => $info,
        ];
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

    /**
     * @return string
     */
    public function isComplete()
    {
        return $this->getSession()->getWebUrl();
    }

    /**
     * @param array $res
     */
    private function writeCurlInfo(array $res)
    {
        $this->writeln('');

        $this->writeBoundary('Request', 'info');
        $this->writeln(sprintf('<info>%s</info>', ($res['info']['request_header']) ?: ($res['method'].' '.$res['url'])));
        $this->writeln('');

        $this->writeBoundary('Response Headers', 'info');
        $this->writeln(sprintf('<info>%s</info>', @$res['headers'] ?: ''));
        $this->writeln('');

        $this->writeBoundary('Response Body', 'info');
        if ($res['result']) {
            $this->writeln(sprintf('<info>%s</info>', substr($res['result'], 0, 300)));
        } else {
            $this->writeln(sprintf('<info>%s</info>', '(no body)'));
        }
    }
}
