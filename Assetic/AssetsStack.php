<?php


namespace ES\Bundle\BaseBundle\Assetic;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AssetsStack extends ContainerAware
{
	private $javascriptIncludes = array();

	private $javascriptCode = array();

	private $cssIncludes = array();

	function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
	}

	public function appendJavascriptInclude($src, $key = null)
	{
		if (null === $key) {
			$key = md5($src);
		}

		if (isset($this->javascriptIncludes[$key])) {
			throw new \RuntimeException(sprintf('Javascript source with key "%s" has already be included.'));
		}

		$this->javascriptIncludes[$key] = $src;
	}

	public function appendCSSInclude($src, $key = null)
	{
		if (null === $key) {
			$key = md5($src);
		}

		if (isset($this->cssIncludes[$key])) {
			throw new \RuntimeException(sprintf('CSS source with key "%s" has already be included.', $key));
		}

		$this->cssIncludes[$key] = $src;
	}

	public function appendJavascriptCode($code)
	{
		if ($this->container->isScopeActive('request') && $this->container->get('request')->isXmlHttpRequest()) {
			return $code;
		}

		$key = md5($code);
		if (isset($this->javascriptCode[$key])) {
			throw new \RuntimeException(sprintf('Javascript code has already be included.'));
		}

		$this->javascriptCode[$key] = $code;
	}

	public function getCSSIncludes()
	{
		return $this->cssIncludes;
	}

	public function getJavascriptIncludes()
	{
		return $this->javascriptIncludes;
	}

	public function getJavascriptCode()
	{
		return preg_replace('#</script>\s*<script>#ius', '', implode("\n", $this->javascriptCode));
	}
} 