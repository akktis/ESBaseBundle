<?php

namespace ES\Bundle\BaseBundle\Entity\Traits;

use Gedmo\Mapping\Annotation as Gedmo;

trait TimestampableTrait
{
	/**
	 * @Gedmo\Timestampable(on="create")
	 * @ORM\Column(type="datetime", name="created_at")
	 */
	protected $createdAt;

	/**
	 * @Gedmo\Timestampable(on="update")
	 * @ORM\Column(type="datetime", name="updated_at")
	 */
	protected $updatedAt;

	/**
	 * Sets createdAt.
	 *
	 * @param  \DateTime $createdAt
	 * @return $this
	 */
	public function setCreatedAt(\DateTime $createdAt)
	{
		$this->createdAt = $createdAt;

		return $this;
	}

	/**
	 * Returns createdAt.
	 *
	 * @return \DateTime
	 */
	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	/**
	 * Sets updatedAt.
	 *
	 * @param  \DateTime $updatedAt
	 * @return $this
	 */
	public function setUpdatedAt(\DateTime $updatedAt)
	{
		$this->updatedAt = $updatedAt;

		return $this;
	}

	/**
	 * Returns updatedAt.
	 *
	 * @return \DateTime
	 */
	public function getUpdatedAt()
	{
		return $this->updatedAt;
	}
}
