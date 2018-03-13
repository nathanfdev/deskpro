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

namespace DpTest\DeskPRO\Bundle\AppBundle\Webhooks\WebhookExecutor;

use Application\DeskPRO\Tickets\TicketManager;
use DeskPRO\Bundle\AppBundle\Entity\Webhooks\TicketWebhook;
use DeskPRO\Bundle\AppBundle\Webhooks\PayloadConvertersRegistry;
use DeskPRO\Bundle\AppBundle\Webhooks\TicketWebhookExecutor;
use DeskPRO\Bundle\AppBundle\Webhooks\WebhookException;
use DeskPRO\Bundle\AppBundle\Webhooks\WebhookHttpRequest;
use DpTest\DeskProTestCase;

class TicketWebhookExecutorTest extends DeskProTestCase
{
    public function testExecuteThrowsExceptionWhenWebhookDoesNotAcceptPayload()
    {
        $webhook = new TicketWebhook();
        $request = new WebhookHttpRequest('', [], json_encode(['key' => 'value']), []);

        /** @var TicketManager | \PHPUnit_Framework_MockObject_MockObject $ticketManager */
        $ticketManager = $this->getMockBuilder(TicketManager::class)->disableOriginalConstructor()->getMock();
        $executor = new TicketWebhookExecutor(new PayloadConvertersRegistry(), $ticketManager);

        $actualException = null;
        try {
            $executor->execute($webhook, $request);
        } catch (WebhookException $e) {
            $actualException = $e;
        }

        $this->assertNotNull($actualException);
        $this->assertEquals(WebhookException::CODE_PAYLOAD_FORBIDDEN, $actualException->getCode());

    }

    public function testExecuteThrowsExceptionWhenDecoderNotFound()
    {
        $webhook = new TicketWebhook();
        $webhook->setPayloadDecoder('some-random-name');
        $converterRegistry = new PayloadConvertersRegistry();
        $request = new WebhookHttpRequest('', [], json_encode(['key' => 'value']), []);

        /** @var TicketManager | \PHPUnit_Framework_MockObject_MockObject $ticketManager */
        $ticketManager = $this->getMockBuilder(TicketManager::class)->disableOriginalConstructor()->getMock();
        $executor = new TicketWebhookExecutor($converterRegistry, $ticketManager);

        $actualException = null;
        try {
            $executor->execute($webhook, $request);
        } catch (WebhookException $e) {
            $actualException = $e;
        }

        $this->assertNotNull($actualException);
        $this->assertEquals(WebhookException::CODE_DECODER_NOT_FOUND, $actualException->getCode());
    }
}
