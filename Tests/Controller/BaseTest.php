<?php

namespace ES\Bundle\BaseBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use ES\Bundle\UserBundle\Model\User;
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

	/**
	 * @param string $userClass
	 * @param string $username
	 * @return User
	 */
	protected function getUser($userClass, $username)
	{
		return $this->getData($userClass, array('username' => $username));
	}

	protected function getData($dataClass, array $criteria)
	{
		return self::getEntityManager()->getRepository($dataClass)->findOneBy($criteria);
	}
}
