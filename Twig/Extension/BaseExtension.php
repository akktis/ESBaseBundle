<?php
/*
 * This file is part of the ESCameleonBundle package.
 */

namespace ES\Bundle\BaseBundle\Twig\Extension;

use ES\Bundle\BaseBundle\Assetic\AssetsStack;
use IntlDateFormatter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class BaseExtension extends \Twig_Extension
{
	private $useBootstrapCdn;

	private $container;

	private $assetsStack;

	private $cameleonGlobals;

	public function __construct(ContainerInterface $container, AssetsStack $assetsStack, array $cameleonGlobals)
	{
		$this->container       = $container;
		$this->assetsStack     = $assetsStack;
		$this->cameleonGlobals = $cameleonGlobals;
	}

	public function setGlobal($key, $value)
	{
		$this->cameleonGlobals[$key] = $value;
	}

	public function getFilters()
	{
		return array(
			'dump'          => new \Twig_Filter_Function(array('ES\Bundle\BaseBundle\Util\Debug', 'dump')),
			'ucfirst'       => new \Twig_Filter_Function('ucfirst'),
			'floor'         => new \Twig_Filter_Function('floor'),
			'ceil'          => new \Twig_Filter_Function('ceil'),
			'urlize'        => new \Twig_Filter_Function(array('ES\Bundle\BaseBundle\Util\Url', 'urlize')),
			'fix_scheme'    => new \Twig_Filter_Function(array('ES\Bundle\BaseBundle\Util\Url', 'fixScheme')),
			'localizeddate' => new \Twig_Filter_Function(array(
					$this,
					'twig_localized_date_filter'
				), array('needs_environment' => true)),
		);
	}

	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction('append_js_include', array($this->assetsStack, 'appendJavascriptInclude')),
			new \Twig_SimpleFunction('append_css_include', array($this->assetsStack, 'appendCSSInclude')),
			new \Twig_SimpleFunction('append_js_code', array($this->assetsStack, 'appendJavascriptCode')),
			new \Twig_SimpleFunction('get_js_code', array($this->assetsStack, 'getJavascriptCode')),
			new \Twig_SimpleFunction('get_js_includes', array($this->assetsStack, 'getJavascriptIncludes')),
			new \Twig_SimpleFunction('get_css_includes', array($this->assetsStack, 'getCSSIncludes')),
			new \Twig_SimpleFunction('now', array($this, 'now')),
		);
	}

	public function now()
	{
		return new \DateTime();
	}

	public function getGlobals()
	{
		return array(
			'cameleon' => $this->cameleonGlobals,
		);
	}

	public function twig_localized_date_filter(\Twig_Environment $env, $date, $dateFormat = 'medium', $timeFormat = 'medium', $locale = null, $timezone = null, $format = null)
	{
		$date = twig_date_converter($env, $date, $timezone);

		$formatValues = array(
			'none'   => IntlDateFormatter::NONE,
			'short'  => IntlDateFormatter::SHORT,
			'medium' => IntlDateFormatter::MEDIUM,
			'long'   => IntlDateFormatter::LONG,
			'full'   => IntlDateFormatter::FULL,
		);

		$formatter = IntlDateFormatter::create(
			$locale,
			$formatValues[$dateFormat],
			$formatValues[$timeFormat],
			$date->getTimezone()->getName(),
			IntlDateFormatter::GREGORIAN,
			$format
		);

		return $formatter->format($date->getTimestamp());
	}

	public function getName()
	{
		return 'es_base';
	}
}