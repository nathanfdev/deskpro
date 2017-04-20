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

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Command;

use Application\DeskPRO\Entity\EmailSource;
use Faker\Factory as Faker;
use Orb\Util\Strings;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenTestIncomingEmailCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dp:email:test-in');
        $this->addArgument('from', InputArgument::REQUIRED, 'The address that the email sholud be sent from. This must be an existing outgoing account in DeskPRO.');
        $this->setHelp('Creates the random email and saves as EmailSource. Can be processed then by dp:process-email --source=X');
    }

    /**
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    public function getContainer()
    {
        return parent::getContainer();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mailer = $this->getContainer()->getMailer();
        $em     = $this->getContainer()->getEm();
        $faker  = Faker::create();

        $date = date('D, jS M Y g:ia');
        $from = $input->getArgument('from');

        $email_accounts = $this->getContainer()->getEmailAccountManager();
        if (!$account = $email_accounts->getPrimaryTicketAccount()) {
            throw new \Exception('No ticket account defined');
        }
        $to = $account->getUseEmailAddress();

        $message = $mailer->createMessage();
        $message->setTo($to);
        $message->setFrom($from);
        $message->setCc([$faker->email, $faker->email]);
        $message->setSubject($faker->sentence().' - '.$date);
        $message->setBody($faker->paragraph());

        $raw_source = trim($message->toString());
        $header_end = strpos($raw_source, "\r\n\r\n");

        if ($header_end === false) {
            // Means an empty body (eg message with only subject)
            // But we trimmed above so the \n\n sep would be trimmed off
            $raw_source .= "\r\n\r\n";
            $header_end = strpos($raw_source, "\r\n\r\n");
        }

        $raw_headers = trim(substr($raw_source, 0, $header_end));

        $reader = $this->getContainer()->getEmailEzcReaderFactory()->create();
        $reader->setEmailAccount($account);
        $reader->setRawSource($raw_source);

        $source = new EmailSource();
        $source->fromArray([
            'email_account' => $account,
            'headers'       => $raw_headers,
            'status'        => 'inserted',
        ]);

        // Rough matching, just for info purposes when browsing a list
        $source->header_to      = Strings::extractRegexMatch('#^To:\s*(.*?)$#m', $raw_headers) ?: '';
        $source->header_cc      = Strings::extractRegexMatch('#^Cc:\s*(.*?)$#m', $raw_headers) ?: '';
        $source->header_from    = Strings::extractRegexMatch('#^From:\s*(.*?)$#m', $raw_headers) ?: '';
        $source->header_subject = Strings::extractRegexMatch('#^Subject:\s*(.*?)$#m', $raw_headers) ?: '';

        $output->writeln('<info>Saving blob...</info>');

        $blob = $this->getContainer()->getBlobStorage()->createBlobRecordFromString(
            $raw_source,
            'email.eml',
            'message/rfc822'
        );

        $source->blob = $blob;

        // Set the copied raw source or else $source->getRawSource() will
        // attempt to load it from the blob storage which is wasteful (eg could read back from s3 what we just wrote)
        $source->_raw = $raw_source;

        $em->persist($source);
        $em->flush();

        $output->writeln(sprintf('<info>Source created: %s</info>', $source->id));
    }
}
