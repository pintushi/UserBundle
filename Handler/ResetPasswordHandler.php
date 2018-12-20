<?php

declare(strict_types=1);

namespace Pintushi\Bundle\UserBundle\Handler;

use Pintushi\Bundle\UserBundle\Repository\UserRepository;
use Pintushi\Bundle\UserBundle\Request\ResetPassword;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Webmozart\Assert\Assert;
use Pintushi\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Pintushi\ShopApiPlugin\Handler\HandlerInterface;

final class ResetPasswordHandler implements HandlerInterface
{
    /** @var UserRepository */
    private $userRepository;
    private $tokenStorage;

    public function __construct(
        UserRepository $userRepository,
        TokenStorageInterface $tokenStorage

    ) {
        $this->userRepository = $userRepository;
        $this->tokenStorage = $tokenStorage;
    }

    public function handle(ResetPassword $command)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        Assert::isInstanceOf($user, User::class);

        $user->setPlainPassword($command->getPassword());

        $this->userRepository->add($user);

        return $user;
    }
}
