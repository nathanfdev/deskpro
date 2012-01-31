<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\UserBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

class TwitterController extends AbstractController
{
    /**
     * Display a long message to the user
     */
    public function messageAction($id)
    {
        // Get the twitter status
        $status = App::getEntityRepository('DeskPRO:TwitterStatus')->find($id);

        if (!$status) {
                throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(sprintf('There is no message with ID "%d"', $id));
        }

        return $this->render('UserBundle:Twitter:message.html.twig', array(
            'status' => $status
        ));
    }

}
