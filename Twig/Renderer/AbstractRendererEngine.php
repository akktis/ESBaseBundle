<?php

namespace ES\Bundle\BaseBundle\Twig\Renderer;

abstract class AbstractRendererEngine
{
	/**
	 * @var array
	 */
	protected $defaultThemes;

	/**
	 * @var array
	 */
	protected $themes = array();

	/**
	 * @var array
	 */
	protected $resources = array();

	/**
	 * @var array
	 */
	private $resourceHierarchyLevels = array();

	/**
	 * Creates a new renderer engine.
	 *
	 * @param array $defaultThemes The default themes. The type of these
	 *                             themes is open to the implementation.
	 */
	public function __construct(array $defaultThemes = array())
	{
		$this->defaultThemes = $defaultThemes;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setTheme(ItemInterface $item, $themes)
	{
		$cacheKey = $item->getUniqueId();

		// Do not cast, as casting turns objects into arrays of properties
		$this->themes[$cacheKey] = is_array($themes) ? $themes : array($themes);

		// Unset instead of resetting to an empty array, in order to allow
		// implementations (like TwigRendererEngine) to check whether $cacheKey
		// is set at all.
		unset($this->resources[$cacheKey]);
		unset($this->resourceHierarchyLevels[$cacheKey]);
	}

	public function getResourceForBlockName(ItemInterface $item, $blockName)
	{
		$cacheKey = $item->getUniqueId();

		if (!isset($this->resources[$cacheKey][$blockName])) {
			$this->loadResourceForBlockName($cacheKey, $item, $blockName);
		}

		return $this->resources[$cacheKey][$blockName];
	}

	public function getResourceForBlockNameHierarchy(ItemInterface $item, array $blockNameHierarchy, $hierarchyLevel)
	{
		$cacheKey  = $item->getUniqueId();
		$blockName = $blockNameHierarchy[$hierarchyLevel];

		if (!isset($this->resources[$cacheKey][$blockName])) {
			$this->loadResourceForBlockNameHierarchy($cacheKey, $item, $blockNameHierarchy, $hierarchyLevel);
		}

		return $this->resources[$cacheKey][$blockName];
	}

	public function getResourceHierarchyLevel(ItemInterface $item, array $blockNameHierarchy, $hierarchyLevel)
	{
		$cacheKey  = $item->getUniqueId();
		$blockName = $blockNameHierarchy[$hierarchyLevel];

		if (!isset($this->resources[$cacheKey][$blockName])) {
			$this->loadResourceForBlockNameHierarchy($cacheKey, $item, $blockNameHierarchy, $hierarchyLevel);
		}

		// If $block was previously rendered loaded with loadTemplateForBlock(), the template
		// is cached but the hierarchy level is not. In this case, we know that the  block
		// exists at this very hierarchy level, so we can just set it.
		if (!isset($this->resourceHierarchyLevels[$cacheKey][$blockName])) {
			$this->resourceHierarchyLevels[$cacheKey][$blockName] = $hierarchyLevel;
		}

		return $this->resourceHierarchyLevels[$cacheKey][$blockName];
	}

	/**
	 * Loads the cache with the resource for a given block name.
	 *
	 * @see getResourceForBlock()
	 *
	 * @param string        $cacheKey  The cache key of the form view.
	 * @param ItemInterface $item      The item for finding the applying themes.
	 * @param string        $blockName The name of the block to load.
	 *
	 * @return bool    True if the resource could be loaded, false otherwise.
	 */
	abstract protected function loadResourceForBlockName($cacheKey, ItemInterface $item, $blockName);

	/**
	 * Loads the cache with the resource for a specific level of a block hierarchy.
	 *
	 * @see getResourceForBlockHierarchy()
	 *
	 * @param string        $cacheKey           The cache key used for storing the
	 *                                          resource.
	 * @param ItemInterface $item               The item for finding the applying
	 *                                          themes.
	 * @param array         $blockNameHierarchy The block hierarchy, with the most
	 *                                          specific block name at the end.
	 * @param int           $hierarchyLevel     The level in the block hierarchy that
	 *                                          should be loaded.
	 *
	 * @return bool    True if the resource could be loaded, false otherwise.
	 */
	private function loadResourceForBlockNameHierarchy($cacheKey, ItemInterface $item, array $blockNameHierarchy, $hierarchyLevel)
	{
		$blockName = $blockNameHierarchy[$hierarchyLevel];

		// Try to find a template for that block
		if ($this->loadResourceForBlockName($cacheKey, $item, $blockName)) {
			// If loadTemplateForBlock() returns true, it was able to populate the
			// cache. The only missing thing is to set the hierarchy level at which
			// the template was found.
			$this->resourceHierarchyLevels[$cacheKey][$blockName] = $hierarchyLevel;

			return true;
		}

		if ($hierarchyLevel > 0) {
			$parentLevel     = $hierarchyLevel - 1;
			$parentBlockName = $blockNameHierarchy[$parentLevel];

			// The next two if statements contain slightly duplicated code. This is by intention
			// and tries to avoid execution of unnecessary checks in order to increase performance.

			if (isset($this->resources[$cacheKey][$parentBlockName])) {
				// It may happen that the parent block is already loaded, but its level is not.
				// In this case, the parent block must have been loaded by loadResourceForBlock(),
				// which does not check the hierarchy of the block. Subsequently the block must have
				// been found directly on the parent level.
				if (!isset($this->resourceHierarchyLevels[$cacheKey][$parentBlockName])) {
					$this->resourceHierarchyLevels[$cacheKey][$parentBlockName] = $parentLevel;
				}

				// Cache the shortcuts for further accesses
				$this->resources[$cacheKey][$blockName]               = $this->resources[$cacheKey][$parentBlockName];
				$this->resourceHierarchyLevels[$cacheKey][$blockName] = $this->resourceHierarchyLevels[$cacheKey][$parentBlockName];

				return true;
			}

			if ($this->loadResourceForBlockNameHierarchy($cacheKey, $item, $blockNameHierarchy, $parentLevel)) {
				// Cache the shortcuts for further accesses
				$this->resources[$cacheKey][$blockName]               = $this->resources[$cacheKey][$parentBlockName];
				$this->resourceHierarchyLevels[$cacheKey][$blockName] = $this->resourceHierarchyLevels[$cacheKey][$parentBlockName];

				return true;
			}
		}

		// Cache the result for further accesses
		$this->resources[$cacheKey][$blockName]               = false;
		$this->resourceHierarchyLevels[$cacheKey][$blockName] = false;

		return false;
	}
}
