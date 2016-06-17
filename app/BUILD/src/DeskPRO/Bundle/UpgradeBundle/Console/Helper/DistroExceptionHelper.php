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

namespace DeskPRO\Bundle\UpgradeBundle\Console\Helper;

use DeskPRO\Bundle\UpgradeBundle\Distro\DistroManifestLoader;
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

    public function renderManifestFailure(\Exception $e, DistroManifestLoader $distroLoader)
    {
        $output = $this->output;

        $output->writeln('<error>Could not load version data from the DeskPRO distribution server.</error>');
        $output->writeln('The two most common reasons for this error are:');
        $output->writeln('  * You have a firewall that is preventing the network connection to the server.');
        $output->writeln('  * The distribution server is temporarily unavailable.');
        $output->writeln('');
        $output->writeln('This is the URL the system is trying to access:');
        $output->writeln('  '.$distroLoader->getApiUrl().DistroManifestLoader::MANIFEST_ENDPOINT);
        $output->writeln('');

        $output->writeln('Here is the error message returned:');
        $this->renderException($e);
    }

    public function renderDownloadFailure(\Exception $e, $zipUrl)
    {
        $output = $this->output;

        $output->writeln('<error>Could not load download the DeskPRO ZIP file.</error>');
        $output->writeln('The two most common reasons for this error are:');
        $output->writeln('  * You have a firewall that is preventing the network connection to the server.');
        $output->writeln('  * The distribution server is temporarily unavailable.');
        $output->writeln('');
        $output->writeln('This is the URL the system is trying to access:');
        $output->writeln('  '.$zipUrl);
        $output->writeln('');

        $output->writeln('Here is the error message returned:');
        $this->renderException($e);
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
