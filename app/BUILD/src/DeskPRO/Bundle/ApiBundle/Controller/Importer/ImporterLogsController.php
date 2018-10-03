<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Importer;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Entity\ImportLog;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class ImporterLogsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/importer_logs")
 * @ApiUserContext("admin")
 * @ApiDoc(target="all", section="Importer", output="DeskPRO\Bundle\AppBundle\Entity\ImportLog")
 */
class ImporterLogsController extends CrudController
{
    public static $entity     = ImportLog::class;
    public static $exposeOnly = ['get', 'list', 'count'];
}
