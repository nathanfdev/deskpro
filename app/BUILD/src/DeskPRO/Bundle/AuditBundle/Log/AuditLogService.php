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

namespace DeskPRO\Bundle\AuditBundle\Log;

use DeskPRO\Bundle\AuditBundle\Entity\AuditLogData;
use DeskPRO\Bundle\AuditBundle\Storage\StorageInterface;
use DeskPRO\Bundle\AuditBundle\Storage\TransformerInterface;
use DeskPRO\Component\Util\ControllerUtils;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AuditLogService.
 */
class AuditLogService
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var TransformerInterface
     */
    private $transformer;

    /**
     * @var AuditLogHelper
     */
    private $helper;

    /**
     * AuditLogService constructor.
     *
     * @param StorageInterface     $storage
     * @param TransformerInterface $transformer
     * @param AuditLogHelper       $helper
     */
    public function __construct(StorageInterface $storage, TransformerInterface $transformer, AuditLogHelper $helper)
    {
        $this->storage     = $storage;
        $this->transformer = $transformer;
        $this->helper      = $helper;
    }

    /**
     * @param AuditLog $log
     */
    public function write(AuditLog $log)
    {
        $concreteLog = $this->transformer->transform($log);
        $this->storage->write($concreteLog);
    }

    /**
     * @param string|Request $action
     * @param string         $description
     * @param AuditLogData   $data
     */
    public function writeAction($action, $description = '', AuditLogData $data = null)
    {
        if ($action instanceof Request) {
            $action = $this->extractAction($action);
        }

        $log = $this->helper->createAuditLog()->setAction($action)->setDescription($description);

        if ($data) {
            $log->setData($data);
        }

        if (!$description && $action instanceof Request) {
            $log->setDescription($this->extractDescription($action, $log));
        }

        $this->write($log);
    }

    private function extractAction(Request $request)
    {
        $controller = $request->attributes->get('_controller');
        $controller = explode('::', $controller);

        return ControllerUtils::calculateTag($controller[0], $controller[1]);
    }

    private function extractDescription(Request $request, AuditLog $log)
    {
        $parts           = explode('::', $request->attributes->get('_controller'));
        $controllerParts = explode('\\', $parts[0]);

        return sprintf(
            '%s performed %s request to %s controller, method is %s',
            $log->getPerformerName(),
            $request->getMethod(),
            array_pop($controllerParts),
            $parts[1]
        );
    }

    /**
     * @param $id
     *
     * @return AuditLog
     */
    public function getLog($id)
    {
        $concreteLog = $this->storage->find($id);

        return $this->transformer->reverseTransform($concreteLog);
    }

    /**
     * @param $offset
     * @param $limit
     *
     * @return AuditLog
     */
    public function read($offset, $limit)
    {
        $concreteLogs = $this->storage->read($offset, $limit);

        return $this->transformer->reverseTransformCollection($concreteLogs);
    }
}
