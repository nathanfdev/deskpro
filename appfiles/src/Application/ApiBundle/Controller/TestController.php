<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage ApiBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\ApiBundle\Controller;

/**
 * A Test API resource
 */
class TestController extends AbstractController
{
	/**
	 * This action simply returns a message to indicate that the API is working
	 */
	public function testAction()
	{
		return $this->createApiResponse(array('message' => 'It works!'));
	}


	/**
	 * Another test action to indicate the POST API is working
	 */
	public function postTestAction()
	{
		$message = isset($_POST['message']) ? $_POST['message'] : 'Post works!';
		return $this->createApiResponse(array('message' => $message));
	}
}