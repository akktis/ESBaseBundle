<?php

namespace ES\Bundle\BaseBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class GMapLocationValidator extends ConstraintValidator
{
	private $fieldsMapping = [
		'country'       => [
			'country',
		],
		'locality'      => [
			'locality',
			'country',
		],
		'route'         => [
			'route',
			'locality',
			'country',
		],
		'street_number' => [
			'street_number',
			'route',
			'locality',
			'country',
		],
	];

	/**
	 * Indicates whether the constraint is valid
	 *
	 * @param array      $location
	 * @param Constraint $constraint
	 */
	public function validate($location, Constraint $constraint)
	{
		$minLevel = $constraint->minLevel;

		if (!is_array($location) || !$location['latitude']) {
			return;
		}

		if (!isset($this->fieldsMapping[$constraint->minLevel])) {
			throw new \InvalidArgumentException(sprintf('Invalid level "%s". Available levels are "%s"',
				$constraint->minLevel,
				implode('", "', array_keys($this->fieldsMapping))
			));
		}

		$requirements = $this->fieldsMapping[$constraint->minLevel];

		foreach ($requirements as $requirement) {
			if (!array_key_exists($requirement, $location)) {
				throw new \InvalidArgumentException(sprintf('Missing "%s" key on location.', $requirement));
			}
			if (!$location[$requirement]) {
				$this->context->addViolation($constraint->minLevelMessages[$minLevel], ['%level%' => $minLevel]);
			}
		}
	}
}
