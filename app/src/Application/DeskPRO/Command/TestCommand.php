<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace Application\DeskPRO\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Client as GuzzleClient;

class TestCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dp:test');
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
        $payload = [
            'text' => '#13 <http://localhost:8080/agent/#app.tickets|Test Slack> New ticket by Admin Admin &lt;julien.ducro@deskpro.com&gt;',
            'channel' => '#general',
            'username' => 'DeskPro',
        ];
//        $ch = curl_init('https://hooks.slack.com/services/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');
//
//        $payload = curl_escape($ch, json_encode($payload));
//        curl_setopt($ch,CURLOPT_POSTFIELDS, 'payload='.$payload);
//        
//        curl_exec($ch);

            
        $client = new GuzzleClient(['base_uri' => 'https://hooks.slack.com/services/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']);

        $request = $client->request('POST', 'https://hooks.slack.com/services/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', ['form_params' => ['payload' => json_encode($payload)]]);

        echo $request->getStatusCode() . "\n";
//        echo $request->getBody();
        echo "\n";

        return 0;
    }
}
