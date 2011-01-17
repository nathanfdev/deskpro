<?php

namespace Application\DevBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DataGeneratorController extends Controller
{
    public function indexAction()
    {
		$dbname_generated = 'test_database_' . date('Ymd_Hi');

		return $this->render('DevBundle:DataGenerator:index.twig.html');
    }
}
