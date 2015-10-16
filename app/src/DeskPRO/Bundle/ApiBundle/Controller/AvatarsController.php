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
namespace DeskPRO\Bundle\ApiBundle\Controller;

use Application\DeskPRO\Entity\Person;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AvatarsController.
 */
class AvatarsController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="Get avatar",
     *      requirements={
     *          {
     *              "name"="target",
     *              "requirement"="person|organization",
     *              "description"="target entity",
     *              "dataType"="string"
     *          },
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the target",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      }
     * )
     * @Get(
     *     "/avatars/{target}/{id}",
     *     name="api_avatar_get",
     *     requirements={
     *         "target" = "person|organization",
     *         "id" = "\d+"
     *     }
     * )
     *
     * @param string $target
     * @param int    $id
     *
     * @return View
     */
    public function getAction($target, $id)
    {
        $targetEntity = $this->findOr404('DeskPRO:'.ucfirst($target), $id, 'Target entity not found');

        /* @var \DeskPRO\Bundle\AppBundle\Content\AvatarResolver $avatarResolver */
        $avatarResolver = $this->get('avatar_resolver');

        $data = [
            'url'         => $avatarResolver->getAvatar($targetEntity),
            'url_pattern' => $avatarResolver->getAvatarPattern($targetEntity, '{{IMG_SIZE}}'),
        ];
        if ($targetEntity instanceof Person) {
            $data['gravatar'] = $targetEntity->getGravatarUrl();
        }

        return View::create($data, Response::HTTP_OK);
    }
}
