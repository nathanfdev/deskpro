<?php

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
