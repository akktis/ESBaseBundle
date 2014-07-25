<?php

namespace ES\Bundle\BaseBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ESBaseExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

		if (isset($config['google_analytics'])) {
			$googleAnalytics = $config['google_analytics'];
			if (!$googleAnalytics['website_name']) {
				throw new InvalidArgumentException('You must configure the website_name at ' . $this->getAlias() . '.google_analytics');
			}
			$loader->load('google_analytics.yml');
			$container->setParameter('es_base.google_analytics.website_name', $googleAnalytics['website_name']);
			$container->setParameter('es_base.google_analytics.trackers', $googleAnalytics['trackers']);
		}

		$config['host_env'] = strpos($config['host_env'], '%') === 0 ?
			$container->getParameter(substr($config['host_env'], 1, -1)) : $config['host_env'];
		// TODO
//		if (in_array($config['host_env'], $config['staging']['secured_environments'])) {
//			$this->loadStaging($loader, $container, $config['staging']);
//		}

		$container->setParameter('es_base.templating.bootstrap.use_cdn', $config['templating']['bootstrap']['use_cdn']);
    }
}
