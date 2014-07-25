<?php

namespace ES\Bundle\BaseBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Templating\EngineInterface;

class GoogleAnalyticsExtension extends \Twig_Extension
{
	/**
	 * @var string
	 */
	protected $webSiteName;

	/**
	 * @var array
	 */
	protected $trackers = array();

	/**
	 * @var ContainerInterface
	 */
	protected $container;

	function __construct(ContainerInterface $container, $webSiteName, array $trackers = array())
	{
		$this->container   = $container;
		$this->webSiteName = $webSiteName;
		$this->trackers    = $trackers;
	}

	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction('es_google_analytics_code', array($this,
				'getGoogleAnalyticsCode'), array('is_safe' => array('html'))),
		);
	}

	public function getGoogleAnalyticsCode($tracker)
	{
		return $this->container->get('templating')->render('ESBaseBundle:Helper:google_analytics.html.twig', array(
			'website_name' => $this->webSiteName,
			'tracker'      => $this->trackers[$tracker],
		));
	}

	/**
	 * Returns the name of the extension.
	 *
	 * @return string The extension name
	 */
	public function getName()
	{
		return 'es_google_analytics';
	}
}