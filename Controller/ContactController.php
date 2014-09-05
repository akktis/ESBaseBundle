<?php
/*
 * This file is part of the ESCameleonBundle package.
 */

namespace ES\Bundle\BaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ContactController extends Controller
{
	public function formAction(Request $request)
	{
		$user = $this->getUser();
		$form = $this->createForm('es_contact_form', null, array(
			'default_email' => $user ? $user->getEmail() : null,
		));

		if ('POST' === $request->getMethod()) {
			$form->handleRequest($request);

			if ($form->isValid()) {
				/** @var \ES\Bundle\BaseBundle\Model\ContactMessage $message */
				$message = $form->getData();
				$message->setUser($user);

				$em = $this->getDoctrine()->getManager();
				$em->persist($message);
				$em->flush();

				$translator = $this->get('translator');
				$this->get('session')->getFlashBag()->add('success', $translator->trans('flashes.contact.sent.success', array(), 'ESBaseBundle'));

				if ($email = $this->container->getParameter('es_cameleon.contact.contact_email')) {
					$this->container->get('es_cameleon.mailer')->send(
						'ESBaseBundle:Mail:contact_message.html.twig',
						$email,
						array('message' => $message),
						$message->getEmail());
				}

				if (!($url = $form->get('redirect_to')->getData()) || !($user = $request->headers->get('referer'))) {
					$url = $this->generateUrl('es_cameleon_contact_form');
				}

				return $this->redirect($url);
			}
		}

		return $this->render($this->container->getParameter('es_cameleon.templates.contact.form'), array(
			'contact_form' => $form->createView(),
		));
	}
} 