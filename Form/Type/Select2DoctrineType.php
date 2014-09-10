<?php

namespace ES\Bundle\BaseBundle\Form\Type;

use Doctrine\Common\Persistence\ManagerRegistry;
use ES\Bundle\BaseBundle\Assetic\AssetsStack;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Templating\Asset\PackageInterface;
use Symfony\Component\Translation\TranslatorInterface;

class Select2DoctrineType extends Select2Type
{
	private $parent;

	public function __construct(AssetsStack $assetsStack, PackageInterface $assetsHelper, TranslatorInterface $translator, $parent)
	{
		parent::__construct($assetsStack, $assetsHelper, $translator);
		$this->parent = $parent;
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
//		$builder->resetViewTransformers();
//
//		if ($options['multiple']) {
//			$builder
//				->addEventSubscriber(new MergeDoctrineCollectionListener())
//				->addViewTransformer(new CollectionToArrayTransformer())
//				->addViewTransformer(new ChoicesToValuesTransformer($options['choice_list']))
//			;
//		} else {
//			$builder->addViewTransformer(new ChoiceToValueTransformer($options['choice_list']));
//		}
	}

	protected function getJSOptions(array $options)
	{
		$jsOptions = array();

		return array_merge(parent::getJSOptions($options), $jsOptions);
	}

	public function getParent()
	{
		return $this->parent;
	}

	public function getName()
	{
		$suffix = $this->parent === 'entity' ? 'orm' : 'mongodb';

		return 'es_select2_' . $suffix;
	}
} 