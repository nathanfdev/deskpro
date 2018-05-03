<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1479817819 extends AbstractBuild
{
    public function run()
    {
        $this->out('Upgrade ApiLog');
        $schemaHelper = $this->getSchemaHelper();
        if (!$schemaHelper->tableHasColumn('api_log', 'is_request_truncated')) {
            $this->execDbQuery('default', 'ALTER TABLE api_log ADD is_request_truncated TINYINT(1) NOT NULL, ADD is_response_truncated TINYINT(1) NOT NULL');
        }
    }
}
