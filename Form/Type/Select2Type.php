<?php

namespace ES\Bundle\BaseBundle\Form\Type;

use ES\Bundle\BaseBundle\Assetic\AssetsStack;
use ES\Bundle\BaseBundle\Form\DataTransformer\ArrayToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Templating\Asset\PackageInterface;
use Symfony\Component\Translation\TranslatorInterface;

class Select2Type extends AbstractType
{
	/**
	 * @var AssetsStack
	 */
	private $assetsStack;

	/**
	 * @var PackageInterface
	 */
	private $assetsHelper;

	/**
	 * @var TranslatorInterface
	 */
	private $translator;

	public function __construct(AssetsStack $assetsStack, PackageInterface $assetsHelper, TranslatorInterface $translator)
	{
		$this->assetsStack  = $assetsStack;
		$this->assetsHelper = $assetsHelper;
		$this->translator   = $translator;

		$this->assetsStack->appendCSSInclude($this->assetsHelper->getUrl('bundles/esbase/vendor/select2/select2.css'));
		$this->assetsStack->appendJavascriptInclude($this->assetsHelper->getUrl('bundles/esbase/vendor/select2/select2.js'));
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->addModelTransformer(new ArrayToStringTransformer($options['value_separator']), true);
	}

	public function finishView(FormView $view, FormInterface $form, array $options)
	{
		$this->assetsStack->appendJavascriptCode('');

		$translator     = $this->translator;
		$transDomain    = $options['translation_domain'];
		$select2Options = array(
			'maximumSelectionSize' => (int)$options['maximum_selection'],
			'placeholder'          => $translator->trans($options['placeholder'], array(), $transDomain),
			'label_searching'      => $translator->trans($options['label_searching'], array(), $transDomain),
			'label_no_matches'     => $translator->trans($options['label_no_matches'], array(), $transDomain),
		);

		$js = json_encode($select2Options);

		$view->vars['js'] = $js;
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$defaults = array(
			'value_separator'   => ',',
			'maximum_selection' => 1,
			'placeholder'       => 'form.select2.placeholder',
			'label_searching'   => 'form.select2.searching',
			'label_no_matches'  => 'form.select2.no_matches',
			'fields_mapping'    => array(
				'name' => 'name',
			),
			'format_result'     => 'function (item){return item.name}',
			'format_selection'  => function (Options $options) {
					return $options['format_result'];
				},
			'renderer'          => null,
		);

		$resolver->setDefaults($defaults);
	}

	public function getParent()
	{
		return 'text';
	}

	public function getName()
	{
		return 'es_select2';
	}
} 