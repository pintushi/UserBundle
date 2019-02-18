<?php

namespace Pintushi\Bundle\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Videni\Bundle\RestBundle\Model\ResourceInterface;
use Pintushi\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Pintushi\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Pintushi\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Pintushi\Bundle\OrganizationBundle\Entity\Organization;

/**
 * @Config(
 *      defaultValues={
 *          "ownership"={
 *              "owner_type"="BUSINESS_UNIT",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="business_unit_owner_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="",
 *              "category"="account_management"
 *          },
 *      }
 * )
 */
class Group implements ResourceInterface
{
    protected $id;

    protected $name;

    protected $roles;

    /**
     * @var BusinessUnit
     */
    protected $owner;

    /**
     * @var Organization
     *
     */
    protected $organization;

    /**
     * @param string $name [optional] Group name
     */
    public function __construct($name = '')
    {
        parent::__construct();

        $this->name  = $name;
        $this->roles = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param  string $name
     * @return Group
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getRoleLabelsAsString()
    {
        $labels = array();
        /** @var $role Role */
        foreach ($this->getRoles() as $role) {
            $labels[] = $role->getLabel();
        }

        return implode(', ', $labels);
    }

    /**
     * Returns the group roles
     * @return Collection The roles
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Get role by string
     * @param  string $roleName Role name
     * @return Role|null
     */
    public function getRole($roleName)
    {
        /** @var $role Role */
        foreach ($this->getRoles() as $role) {
            if ($roleName == $role->getRole()) {
                return $role;
            }
        }

        return null;
    }

    /**
     * @param  Role|string $role
     * @return boolean
     * @throws \InvalidArgumentException
     */
    public function hasRole($role)
    {
        if ($role instanceof Role) {
            $roleName = $role->getRole();
        } elseif (is_string($role)) {
            $roleName = $role;
        } else {
            throw new \InvalidArgumentException(
                '$role must be an instance of Pintushi\Bundle\UserBundle\Entity\Role or a string'
            );
        }

        return (bool)$this->getRole($roleName);
    }

    /**
     * Adds a Role to the Collection
     * @param  Role $role
     * @return Group
     */
    public function addRole(Role $role)
    {
        if (!$this->hasRole($role)) {
            $this->roles->add($role);
        }

        return $this;
    }

    /**
     * Remove the Role object from collection
     * @param  Role|string $role
     * @return Group
     * @throws \InvalidArgumentException
     */
    public function removeRole($role)
    {
        if ($role instanceof Role) {
            $roleObject = $role;
        } elseif (is_string($role)) {
            $roleObject = $this->getRole($role);
        } else {
            throw new \InvalidArgumentException(
                '$role must be an instance of Pintushi\Bundle\UserBundle\Entity\Role or a string'
            );
        }
        if ($roleObject) {
            $this->roles->removeElement($roleObject);
        }

        return $this;
    }

    /**
     * Set new Roles collection
     * @param  array|Collection $roles
     * @return Group
     * @throws \InvalidArgumentException
     */
    public function setRoles($roles)
    {
        if ($roles instanceof Collection) {
            $this->roles = new ArrayCollection($roles->toArray());
        } elseif (is_array($roles)) {
            $this->roles = new ArrayCollection($roles);
        } else {
            throw new \InvalidArgumentException(
                '$roles must be an instance of Doctrine\Common\Collections\Collection or an array'
            );
        }

        return $this;
    }

    /**
     * @return BusinessUnit
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param BusinessUnit $owningBusinessUnit
     * @return Group
     */
    public function setOwner($owningBusinessUnit)
    {
        $this->owner = $owningBusinessUnit;

        return $this;
    }

    /**
     * Return the group name field
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->name;
    }

    /**
     * Set organization
     *
     * @param Organization $organization
     * @return Group
     */
    public function setOrganization(Organization $organization = null)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Get organization
     *
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }
}
