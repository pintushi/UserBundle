<?php

declare(strict_types=1);

namespace Pintushi\Bundle\UserBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccess;

final class UserNotExistValidator extends ConstraintValidator
{
    private $doctrine;

    private static $propertyAccessor;

    public function __construct(
        ManagerRegistry $doctrine
    ) {
        $this->doctrine = $doctrine;
    }

    public function validate($entity, Constraint $constraint)
    {
        if ($value === null) {
            return;
        }

        $userClass = $constraint->userClass;
        if (!class_exists($userClass)) {
            throw new \Exception(sprintf('User class %s is not existed', $userClass));
        }

        $propertyAccessor = $this->getPropertyAccessor();

        $criteria = [];

        foreach ($constraint->fields as $field) {
            $criteria[$field] = $propertyAccessor->getValue($enitty, $field);
        }

        $em = $this->doctrine->getManagerForClass($userClass);

        $repository = $em->getRepository($userClass);

        $user = $repository->findBy($criteria);
        if (null !== $user) {
            $this->context->addViolation($constraint->message);
        }
    }

     /**
     * @return PropertyAccessor
     */
    private static function getPropertyAccessor()
    {
        if (!self::$propertyAccessor) {
            self::$propertyAccessor = PropertyAccess::createPropertyAccessor();
        }

        return self::$propertyAccessor;
    }
}
