<?php

namespace ES\Bundle\BaseBundle\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class ContactListener implements EventSubscriber
{
	protected $contactMessageClass;
	protected $contactMessageTable;

	function __construct($contactMessageClass, $contactMessageTable)
	{
		$this->contactMessageClass = $contactMessageClass;
		$this->contactMessageTable = $contactMessageTable;
	}

	public function getSubscribedEvents()
	{
		return array(
			Events::loadClassMetadata,
		);
	}

	public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
	{
		/** @var ClassMetadataInfo $metadata */
		$metadata = $eventArgs->getClassMetadata();

		$className = $metadata->getName();
		if ($className === $this->contactMessageClass) {
			$metadata->setPrimaryTable(array(
				'name' => $this->contactMessageTable,
			));
			$metadata->isMappedSuperclass = false;
		}
	}
}