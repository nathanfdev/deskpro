<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use Symfony\Component\HttpFoundation\Request;
use Twilio\Exceptions\TwilioException;

/**
 * Class AbstractVoiceCrudController.
 */
abstract class AbstractVoiceCrudController extends CrudController
{
    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        try {
            return parent::handleForm($model, $request, $options);
        } catch (TwilioException $e) {
            return $this->getFormErrorResponseFromException('twilio_exception', $e);
        }
    }
}
