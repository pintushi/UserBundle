<?php

declare(strict_types=1);

namespace Pintushi\Bundle\UserBundle\Entity;

interface UserAwareInterface
{
    /**
     * @return UserInterface|null
     */
    public function getUser(): ?UserInterface;

    /**
     * @param UserInterface|null $user
     */
    public function setUser(?UserInterface $user);
}
