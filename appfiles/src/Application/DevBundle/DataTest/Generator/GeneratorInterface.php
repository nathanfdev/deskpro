<?php

namespace Application\DevBundle\DataTest\Generator;

interface GeneratorInterface
{
	/**
	 * Generate new data
	 */
	public function generateData($count);
}