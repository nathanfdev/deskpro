<?php

namespace Application\DeskPRO\Usersource;

use Application\DeskPRO\Usersource\Actions\AbstractAction;
use DpSys\LowError\SystemErrorHandler;
use Orb\Types\JsonObjectSerializable;

class ActionsCollection extends \ArrayObject implements JsonObjectSerializable
{
    /**
     * {@inheritdoc}
     */
    public function serializeJsonArray()
    {
        $data = [];

        foreach ($this as $v) {
            $data[] = $v->toArray();
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public static function unserializeJsonArray(array $data)
    {
        $obj = new self();
        foreach ($data as $v) {
            // skip empty actions
            if (empty($v['data'])) {
                continue;
            }

            try {
                $action = AbstractAction::fromArray($v);
                $obj[]  = $action;
            } catch (\Exception $e) {
                SystemErrorHandler::logException($e, false, md5('action_'.$v['type']));
            }
        }

        return $obj;
    }
}
