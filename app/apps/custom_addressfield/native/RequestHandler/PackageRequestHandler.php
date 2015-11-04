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
 *
 * @category Entities
 */
namespace custom_addressfield\RequestHandler;

use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestContext;
use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestHandlerInterface;
use Application\DeskPRO\Entity\CustomDefTicket;

class PackageRequestHandler implements ApiPackageRequestHandlerInterface
{
    const ATTR_KEY = 'data-customadds';

    /**
     * {@inheritdoc}
     */
    public function handleApiPackageRequest(ApiPackageRequestContext $context)
    {
        switch ($context->getAction()) {
            case 'get-fields':
                return $this->getFieldsAction($context);
            case 'selected':
                return $this->selectedAction($context);
            default:
                throw $context->createNotFoundException();
        }
    }

    /**
     * @param ApiPackageRequestContext $context
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getFieldsAction(ApiPackageRequestContext $context)
    {
        $manager = $context->getContainer()->getTicketFieldManager();
        $fields  = array();
        foreach ($manager->getFields() as $field) {
            if ('Textarea' === substr($field->handler_class, strrpos($field->handler_class, '\\') + 1)) {
                $fields[] = array(
                    'label' => $field->title,
                    'value' => $field->id,
                );
            }
        }

        return $context->createJsonResponse(array(
            'url'    => $context->getContainer()->getSetting('core.deskpro_url'),
            'fields' => $fields,
        ));
    }

    /**
     * @param ApiPackageRequestContext $context
     *
     * @return array
     */
    public function selectedAction(ApiPackageRequestContext $context)
    {
        $fieldId = $context->getRequest()->get('field_id');
        $manager = $context->getContainer()->getTicketFieldManager();
        foreach ($manager->getFields() as $field) {
            /** @var $field CustomDefTicket */
            if ('Textarea' !== substr($field->handler_class, strrpos($field->handler_class, '\\') + 1)) {
                continue;
            }

            if ($fieldId != $field->id) {
                $attr = $field->getOption('attr');
                if (isset($attr[self::ATTR_KEY])) {
                    unset($attr[self::ATTR_KEY]);
                }
                $field->setOption('attr', $attr);
                continue;
            }

            $field->setOption('attr', array(self::ATTR_KEY => '1'));
        }

        $context->getEm()->flush();

        return $context->createResponse('');
    }
}
