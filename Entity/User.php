<?php

namespace Pintushi\Bundle\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\Role\Role;
use Pintushi\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Pintushi\Bundle\OrganizationBundle\Entity\BusinessUnitInterface;
use Pintushi\Bundle\OrganizationBundle\Entity\Ownership\BusinessUnitAwareTrait;
use Pintushi\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Pintushi\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Videni\Bundle\RestBundle\Model\ResourceInterface;

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
class User extends AbstractUser implements OrganizationAwareUserInterface, ResourceInterface
{
    use BusinessUnitAwareTrait {
        setOrganization as oldSetOrganization;
    }

    protected $organizations;

    protected $owner;

    protected $businessUnits;

    protected $groups;

    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->organizations = new ArrayCollection();
        $this->businessUnits = new ArrayCollection();
        $this->groups = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getUsername();
    }

    public function setOrganization(?OrganizationInterface $organization)
    {
        $this->oldSetOrganization($organization);

        if ($organization) {
            $this->addOrganization($organization);
        }

        return $this;
    }

     /**
     * Add Organization to User
     *
     * @param OrganizationInterface $organization
     * @return AbstractUser
     */
    public function addOrganization(OrganizationInterface $organization)
    {
        if (!$this->hasOrganization($organization)) {
            $this->getOrganizations()->add($organization);
        }

        return $this;
    }

    /**
     * Whether user in specified organization
     *
     * @param OrganizationInterface $organization
     * @return bool
     */
    public function hasOrganization(OrganizationInterface $organization)
    {
        return $this->getOrganizations()->contains($organization);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizations($onlyActive = false)
    {
        if ($onlyActive) {
            return $this->organizations->filter(
                function (OrganizationInterface $organization) {
                    return $organization->isEnabled() === true;
                }
            );
        }

        return $this->organizations;
    }

    /**
     * @param Collection $organizations
     * @return AbstractUser
     */
    public function setOrganizations(Collection $organizations)
    {
        $this->organizations = $organizations;

        return $this;
    }

    /**
     * Delete Organization from User
     *
     * @param Organization $organization
     * @return AbstractUser
     */
    public function removeOrganization(OrganizationInterface $organization)
    {
        if ($this->hasOrganization($organization)) {
            $this->getOrganizations()->removeElement($organization);
        }

        return $this;
    }

    /**
     * @param BusinessUnitInterface $businessUnit
     *
     * @return $this
     */
    public function addBusinessUnit(BusinessUnitInterface $businessUnit)
    {
        if (!$this->getBusinessUnits()->contains($businessUnit)) {
            $this->getBusinessUnits()->add($businessUnit);
        }

        return $this;
    }

    public function getBusinessUnits(): Collection
    {
        $this->businessUnits = $this->businessUnits ?: new ArrayCollection();

        return $this->businessUnits;
    }

    /**
     * @param Collection $businessUnits
     *
     * @return $this
     */
    public function setBusinessUnits(Collection $businessUnits)
    {
        $this->businessUnits = $businessUnits;

        return $this;
    }

    /**
     * @param BusinessUnitInterface $businessUnit
     *
     * @return $this
     */
    public function removeBusinessUnit(BusinessUnitInterface $businessUnit)
    {
        if ($this->getBusinessUnits()->contains($businessUnit)) {
            $this->getBusinessUnits()->removeElement($businessUnit);
        }

        return $this;
    }

    public function getOwner(): ?BusinessUnitInterface
    {
        return $this->owner;
    }

    /**
     * @param BusinessUnitInterface $owningBusinessUnit
     * @return $this
     */
    public function setOwner(BusinessUnitInterface $owningBusinessUnit)
    {
        $this->owner = $owningBusinessUnit;

        return $this;
    }

      /**
     * Gets the groups granted to the user
     *
     * @return Collection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasGroup($name)
    {
        return (bool)$this
            ->getGroups()
            ->filter(
                function (Group $group) use ($name) {
                    return $group->getName() === $name;
                }
            )
            ->count();
    }

    /**
     * @return array
     */
    public function getGroupNames()
    {
        return $this
            ->getGroups()
            ->map(
                function (Group $group) {
                    return $group->getName();
                }
            )
            ->toArray();
    }

    /**
     * @param Group $group
     *
     * @return User
     */
    public function addGroup(Group $group)
    {
        if (!$this->getGroups()->contains($group)) {
            $this->getGroups()->add($group);
        }

        return $this;
    }

    /**
     * @param Group $group
     *
     * @return User
     */
    public function removeGroup(Group $group)
    {
        if ($this->getGroups()->contains($group)) {
            $this->getGroups()->removeElement($group);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->password,
            $this->salt,
            $this->usernameCanonical,
            $this->username,
            $this->enabled,
            $this->id,
            $this->email,
            $this->emailCanonical,
            $this->organization,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        list(
            $this->password,
            $this->salt,
            $this->usernameCanonical,
            $this->username,
            $this->enabled,
            $this->id,
            $this->email,
            $this->emailCanonical,
            $this->organization
        ) = $data;
    }
}
