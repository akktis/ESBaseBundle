<?php
/*
 * This file is part of the ESCameleonBundle package.
 */

namespace ES\Bundle\BaseBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Routing\RouterInterface;

class ContactFormType extends AbstractType
{
	private $class;

	private $router;

	private $request;

	public function __construct($class, RouterInterface $router, Request $request)
	{
		$this->class   = $class;
		$this->router  = $router;
		$this->request = $request;
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->setAction($this->router->generate('es_cameleon_contact_form'));
		$builder
			->add('redirect_to', 'hidden', array(
				'mapped' => false,
				'data'   => $this->request->getRequestUri(),
			));
		$this->buildContactForm($builder, $options);
	}

	protected function buildContactForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('email', null, array(
				'label'              => 'form.contact.email',
				'translation_domain' => 'ESBaseBundle',
				'data'               => $options['default_email'] ? $options['default_email'] : null,
			))
			->add('message', null, array(
				'attr'               => array(
					'rows' => 6,
				),
				'label'              => 'form.contact.email',
				'translation_domain' => 'ESBaseBundle',
			))
			->add('submit', 'submit', array(
				'label'              => 'form.contact.submit',
				'translation_domain' => 'ESBaseBundle',
			));
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class'    => $this->class,
			'intention'     => 'contact',
			'default_email' => null,
		));
	}

	public function getName()
	{
		return 'es_contact_form';
	}
} 