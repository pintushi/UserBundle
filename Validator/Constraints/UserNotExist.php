<?php

declare(strict_types=1);

namespace Pintushi\Bundle\UserBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

final class UserNotExist extends Constraint
{
    public $fields;

    public $userClass;

    public function getRequiredOptions()
    {
        return array('fields', 'userClass');
    }
    /**
     * @var string
     */
    public $message = '该用户已存在';

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
