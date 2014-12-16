<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\PortalBundle\Themes\DevTest\Controller;


use Application\PortalBundle\Controller\AbstractController;
use Application\PortalBundle\Request\TagRequest;
use Symfony\Component\HttpFoundation\Response;
use Application\PortalBundle\Annotation\Tag;
use Application\PortalBundle\Annotation\TagOptions;

class TestController extends AbstractController
{
    /**
     * // these are an exmaple of two tags that only apply to this theme (we simply created this method and used them in a tempalate)
     *
     * @Tag(name="hello_henry", default_options={"name":"Henry!"})
     * @Tag(name="hello_world")
     *
     * @TagOptions(
     *      defaults={
     *          "name": "World"
     *      }
     * )
     */
    public function testAction(TagRequest $request, array $options)
    {
        return new Response(sprintf('Hello, %s', $options['name']));
    }
}
