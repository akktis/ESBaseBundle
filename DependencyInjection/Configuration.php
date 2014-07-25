<?php

namespace ES\Bundle\BaseBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('es_base');

		$rootNode
			->children()
				->scalarNode('host_env')
					->defaultValue('%cameleon.host_env%')
					->cannotBeEmpty()
				->end()
				->arrayNode('templating')
					->addDefaultsIfNotSet()
					->children()
						->arrayNode('bootstrap')
							->addDefaultsIfNotSet()
							->children()
								->scalarNode('use_cdn')->defaultTrue()->end()
							->end()
						->end()
					->end()
				->end()
				->arrayNode('google_analytics')
					->children()
						->scalarNode('website_name')->defaultValue('%cameleon.project_name%')->end()
						->arrayNode('trackers')
						->example('main: UA-47067754-2')
						->useAttributeAsKey('name')
							->prototype('scalar')
						->end()
					->end()
				->end()
			->end();

        return $treeBuilder;
    }
}
