<?php

namespace DeskPRO\Bundle\UpdateBundle\Console\Helper;

use DeskPRO\Bundle\UpdateBundle\Distro\DistroManifestLoader;
use DeskPRO\Component\Util\DebugUtils;
use DeskPRO\Component\Util\TypeUtils;
use Orb\Util\Strings;
use Symfony\Component\Console\Output\OutputInterface;

class DistroExceptionHelper
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * DistroExceptionHelper constructor.
     *
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param \Exception           $e
     * @param DistroManifestLoader $distroLoader
     *
     * @return string
     */
    public function getManifestFailureDescription(\Exception $e, DistroManifestLoader $distroLoader)
    {
        $lines   = [];
        $lines[] = 'Could not load version data from the DeskPRO distribution server.';
        $lines[] = 'The two most common reasons for this error are:';
        $lines[] = '  * You have a firewall that is preventing the network connection to the server.';
        $lines[] = '  * The distribution server is temporarily unavailable.';
        $lines[] = '';
        $lines[] = 'This is the URL the system is trying to access:';
        $lines[] = '  '.$distroLoader->getApiUrl().DistroManifestLoader::MANIFEST_ENDPOINT;
        $lines[] = '';
        $lines[] = 'Here is the error message returned:';
        $lines[] = DebugUtils::getExceptionSummary($e);

        return implode("\n", $lines);
    }

    /**
     * @param \Exception           $e
     * @param DistroManifestLoader $distroLoader
     */
    public function renderManifestFailure(\Exception $e, DistroManifestLoader $distroLoader)
    {
        $output = $this->output;

        $lines = explode("\n", $this->getManifestFailureDescription($e, $distroLoader));
        foreach ($lines as $idx => $l) {
            if ($idx === 0) {
                $output->writeln('<error>'.$l.'</error>');
            } else {
                $output->writeln($l);
            }
        }
    }

    /**
     * @param \Exception $e
     * @param string     $zipUrl
     *
     * @return string
     */
    public function getDownloadFailureDescription(\Exception $e, $zipUrl)
    {
        $lines   = [];
        $lines[] = '<error>Could not load download the DeskPRO ZIP file.</error>';
        $lines[] = 'The two most common reasons for this error are:';
        $lines[] = '  * You have a firewall that is preventing the network connection to the server.';
        $lines[] = '  * The distribution server is temporarily unavailable.';
        $lines[] = '';
        $lines[] = 'This is the URL the system is trying to access:';
        $lines[] = '  '.$zipUrl;
        $lines[] = '';
        $lines[] = 'Here is the error message returned:';
        $lines[] = DebugUtils::getExceptionSummary($e);

        return implode("\n", $lines);
    }

    /**
     * @param \Exception $e
     * @param string     $zipUrl
     */
    public function renderDownloadFailure(\Exception $e, $zipUrl)
    {
        $output = $this->output;

        $lines = explode("\n", $this->getDownloadFailureDescription($e, $zipUrl));
        foreach ($lines as $idx => $l) {
            if ($idx === 0) {
                $output->writeln('<error>'.$l.'</error>');
            } else {
                $output->writeln($l);
            }
        }
    }

    /**
     * @param \Exception $e
     */
    public function renderException(\Exception $e)
    {
        do {
            $message = $e->getMessage();
            $message = preg_replace('/\s+/', ' ', Strings::stripTags($message));

            $this->output->writeln('<info>'.sprintf('[%s:%s] %s', TypeUtils::getBaseTypeName($e), $e->getCode(),
                    $message).'</info>');
        } while ($e = $e->getPrevious());
    }
}
