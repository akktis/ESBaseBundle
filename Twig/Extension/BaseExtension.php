<?php
/*
 * This file is part of the ESCameleonBundle package.
 */

namespace ES\Bundle\BaseBundle\Twig\Extension;

use ES\CameleonBundle\Manager\ObjectHashManagerInterface;
use IntlDateFormatter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class BaseExtension extends \Twig_Extension
{
	private $useBootstrapCdn;

	private $javascriptBottomIncludes = array();

	private $javascriptBottomCode = array();

	private $cssIncludes = array();

	private $container;

	public function __construct(ContainerInterface $container, $useBootstrapCdn)
	{
		$this->container       = $container;
		$this->useBootstrapCdn = $useBootstrapCdn;
		if (!class_exists('IntlDateFormatter')) {
			throw new RuntimeException('The intl extension is needed to use intl-based filters.');
		}
	}

	/**
	 * Returns the name of the extension.
	 *
	 * @return string The extension name
	 */
	public function getName()
	{
		return 'es_base';
	}

	public function getFilters()
	{
		return array(
			'dump'          => new \Twig_Filter_Function(array('Doctrine\Common\Util\Debug', 'dump')),
			'ucfirst'       => new \Twig_Filter_Function('ucfirst'),
			'floor'         => new \Twig_Filter_Function('floor'),
			'urlize'        => new \Twig_Filter_Function(array($this, 'urlize')),
			'localizeddate' => new \Twig_Filter_Function(array($this,
					'twig_localized_date_filter'), array('needs_environment' => true)),
			'fix_scheme'    => new \Twig_Filter_Function(array($this, 'fixScheme')),
		);
	}

	public function fixScheme($str)
	{
		return preg_replace('#\=("|\')(http|ftp)\:\/\/#u', '=$1//', $str);
	}

	public function urlize($str)
	{
		$str = preg_replace('/(^|\s)((?:http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(?:\/[^\<\s]*)?)/u', '\\1<a href="\\2" target="_blank">\\2</a>', $str);
		$str = preg_replace('/(^|\s)(www\.[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(?:\/[^\<\s]*)?)/u', '\\1<a href="http://\\2" target="_blank">\\2</a>', $str);

		return $str;
	}

	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction('append_js_include', array($this, 'appendJavascriptInclude')),
			new \Twig_SimpleFunction('append_js_code', array($this, 'appendJavascriptCode')),
			new \Twig_SimpleFunction('now', array($this, 'now')),
		);
	}

	public function now()
	{
		return new \DateTime();
	}

	public function appendJavascriptInclude($src)
	{
		$this->javascriptBottomIncludes[] = $src;
	}

	public function appendJavascriptCode($code)
	{
		if ($this->container->get('request')->isXmlHttpRequest()) {
			return $code;
		}
		$this->javascriptBottomCode[] = $code;
	}

	public function appendCSSInclude($src)
	{
		$this->cssIncludes[] = $src;
	}

	public function getGlobals()
	{
		return array(
			'cameleon' => array(
				'host_env'         => $this->container->getParameter('cameleon.host_env'),
				'project_url'      => $this->container->getParameter('cameleon.project_url'),
				'project_name'     => $this->container->getParameter('cameleon.project_name'),
				'project_title'    => $this->container->getParameter('cameleon.project_title'),
				'bootstrap'        => array(
					'use_cdn' => $this->useBootstrapCdn
				),
				'javascriptBottom' => array(
					'code'     => &$this->javascriptBottomCode,
					'includes' => &$this->javascriptBottomIncludes,
				),
				'cssIncludes'      => &$this->cssIncludes,
			)
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
}