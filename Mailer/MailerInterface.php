<?php


namespace ES\Bundle\BaseBundle\Mailer;

use Symfony\Component\Security\Core\User\UserInterface;

interface MailerInterface
{
	/**
	 * @param string       $templateName
	 * @param string|array $toEmail
	 * @param array        $params
	 * @param string|array $fromEmail
	 */
	public function send($templateName, $toEmail, array $params = array(), $fromEmail = null);

	/**
	 * @param string        $templateName
	 * @param array         $params
	 * @param UserInterface $user
	 * @param array|string  $fromEmail
	 */
	public function sendToUser($templateName, array $params = array(), UserInterface $user = null, $fromEmail = null);
} 