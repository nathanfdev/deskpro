<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\Webhooks;

use DeskPRO\Bundle\AppBundle\Webhooks\Converters;
use DeskPRO\Bundle\AppBundle\Webhooks\FormPayloadConverter;
use DpTest\DeskProTestCase;
use Symfony\Component\HttpFoundation\Request;

class FormPayloadConverterTest extends DeskProTestCase
{
    public function testDecodeReturnsFormValues()
    {
        $form = [
            'text_input' => 'some text',
            'webhood' => [
                'isEnabled' => true,
            ]
        ];
        $expected = json_decode(json_encode($form));

        $httpRequest = Request::create('http://localhost');
        $httpRequest->initialize([], $form);
        $httpRequest->setMethod('POST');
        $httpRequest->headers->set('CONTENT_TYPE', 'application/x-www-form-urlencoded');

        $webhookRequest = Converters::toWebhookRequest($httpRequest);

        $converter = new FormPayloadConverter();
        $actual = $converter->decode($webhookRequest);
        $this->assertEquals($expected, $actual);
    }
}
