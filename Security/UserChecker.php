<?php

namespace Pintushi\Bundle\UserBundle\Security;

use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Pintushi\Bundle\UserBundle\Entity\ToggleableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Pintushi\Bundle\UserBundle\Entity\UserInterface as PintushiUserInterface;

class UserChecker implements UserCheckerInterface
{
    /**
     * {@inheritdoc}
     */
    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof ToggleableInterface) {
            return;
        }

        if ($user->isEnabled() === false) {
            $ex = new DisabledException('Your account is disabled, please contact your administrator');
            $ex->setUser($user);
            throw $ex;
        }

        if (!$user instanceof PintushiUserInterface) {
            return;
        }

        if (!$user->isAccountNonExpired()) {
            $ex = new AccountExpiredException('Your account is expired, please contact your administrator');
            $ex->setUser($user);
            throw $ex;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkPostAuth(UserInterface $user)
    {

    }
}
