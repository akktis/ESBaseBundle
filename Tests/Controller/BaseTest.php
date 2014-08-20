<?php

namespace ES\Bundle\BaseBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class BaseTest extends WebTestCase
{
	/**
	 * @return EntityManager
	 */
	static protected function getEntityManager()
	{
		return static::getContainer()->get('doctrine')->getManager();
	}

	static protected function getContainer()
	{
		if (!static::$kernel) {
			static::bootKernel();
		}

		return static::$kernel->getContainer();
	}
}
