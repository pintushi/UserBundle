<?php

namespace Pintushi\Bundle\UserBundle\Entity;

use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Role\Role as BaseRole;

abstract class AbstractRole extends BaseRole
{
    /**
     * @var string
     */
    protected $role;

    abstract public function getPrefix(): string;

    /**
     * Set role name only for newly created role
     *
     * @param  string $role Role name
     * @param bool $generateUnique
     * @return $this
     */
    public function setRole(string $role, bool $generateUnique = true)
    {
        $this->role = $generateUnique
            ? $this->generateUniqueRole($role)
            : $this->addPrefix($this->normalize($role));

        return $this;
    }

    abstract public function getLabel(): string;

    public function __toString(): string
    {
        return (string)$this->getLabel();
    }

    public function generateUniqueRole(string $role = ''): string
    {
        $role = $this->normalize($role);
        $role = $this->addPrefix($role);
        $role = $this->addUniqueSuffix($role);

        return $role;
    }

    protected function normalize(string $role): string
    {
        return strtoupper(preg_replace('/[^\w\-]/i', '_', $role));
    }

    protected function addUniqueSuffix(string $role): string
    {
        return uniqid(rtrim($role, '_') . '_');
    }

    protected function addPrefix(string $role): string
    {
        if ($role !== AuthenticatedVoter::IS_AUTHENTICATED_ANONYMOUSLY && strpos($role, $this->getPrefix()) !== 0) {
            $role = $this->getPrefix() . $role;
        }

        return $role;
    }
}
