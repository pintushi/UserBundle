<?php

namespace Pintushi\Bundle\UserBundle\Entity;

use Pintushi\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Config(
 *      defaultValues={
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="",
 *              "category"="account_management"
 *          },
 *      }
 * )
 */
class Role extends AbstractRole
{
    const PREFIX_ROLE = 'ROLE_';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $role;

    protected $users;

    /**
     * @var string
     */
    protected $label;

    protected $description;

    /**
     * Populate the role field
     *
     * @param string $role ROLE_FOO etc
     */
    public function __construct(string $role = '')
    {
        parent::__construct($role);
        $this->role  =
        $this->label = $role;

        $this->users = new ArrayCollection();
    }

    /**
     * Unset the id on copy
     */
    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->setRole($this->role, false);
        }
    }

    /**
     * Return the role id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Return the role name field
     *
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * Return the role label field
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Set the new label for role
     *
     * @param  string $label New label
     * @return Role
     */
    public function setLabel(string $label): Role
    {
        $this->label = (string)$label;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrefix(): string
    {
        return static::PREFIX_ROLE;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

     /**
     * @param User $user
     *
     * @return $this
     */
    public function addUser(User $user)
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
        }

        return $this;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function removeUser(User $user)
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        $dataForSerialization = [$this->id, $this->role, $this->label];
        if (property_exists($this, 'organization')) {
            $dataForSerialization[] =  is_object($this->organization) ? clone $this->organization : $this->organization;
        }

        return serialize($dataForSerialization);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        if (property_exists($this, 'organization')) {
            list($this->id, $this->role, $this->label, $this->organization) = unserialize($serialized);
        } else {
            list($this->id, $this->role, $this->label) = unserialize($serialized);
        }
    }
}
