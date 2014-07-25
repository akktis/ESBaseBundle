<?php

namespace ES\Bundle\BaseBundle\DependencyInjection;

use ES\Bundle\BaseBundle\Security\Listener\StagingListener;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;
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
				throw new InvalidConfigurationException('You must configure the website_name at ' . $this->getAlias() . '.google_analytics');
			}
			$loader->load('google_analytics.yml');
			$container->setParameter('es_base.google_analytics.website_name', $googleAnalytics['website_name']);
			$container->setParameter('es_base.google_analytics.trackers', $googleAnalytics['trackers']);
		}

		$config['host_env'] = strpos($config['host_env'], '%') === 0 ?
			$container->getParameter(substr($config['host_env'], 1, -1)) : $config['host_env'];

		if (in_array($config['host_env'], $config['staging']['secured_environments'])) {
			$this->loadStaging($loader, $container, $config['staging']);
		}

		$container->setParameter('es_base.templating.bootstrap.use_cdn', $config['templating']['bootstrap']['use_cdn']);
    }

	private function loadStaging($loader, ContainerBuilder $container, $config)
	{
		$askUsername = true;
		if ($config['password']) {
			$askUsername = false;
			if (count($config['users']) > 0) {
				throw new InvalidConfigurationException('You must either set "password" or define "users" in "es_cameleon.staging"');
			}
			if ($config['authtype'] === 'basic') {
				throw new InvalidConfigurationException('Basic authentication does not support default username. You must provide the "users" node instead of the "password" one.');
			}
			$config['users'] = array(
				StagingListener::DEFAULT_USERNAME => array(
					'password' => $config['password'],
					'roles'    => array('ROLE_STAGING')
				)
			);
		}

		$authType = $config['authtype'];
		$loader->load('staging.yml');
		$loader->load(sprintf('auth/%s.yml', $authType));

		$exceptionListener = $container->getDefinition('es_base.security.exception_listener');
		$exceptionListener->replaceArgument(4, new Reference('es_base.security.entry_point.' . $authType));

		$this->compileParameters($container, 'es_base.security.staging', $config);
		$container->setParameter('es_base.security.staging.ask_username', $askUsername);
	}

	private function compileParameters(ContainerBuilder $container, $prefix, array $config)
	{
		foreach ($config as $key => $value) {
			$container->setParameter($prefix . '.' . $key, $value);
		}
	}
}
