<?php

declare(strict_types=1);

namespace Pintushi\Bundle\UserBundle\Provider;

use Symfony\Component\Security\Core\User\UserInterface;

class UsernameProvider extends AbstractUserProvider
{
    /**
     * {@inheritdoc}
     */
    protected function findUser(string $username): ?UserInterface
    {
        return $this->userRepository->findOneBy(['usernameCanonical' => $username]);
    }
}
