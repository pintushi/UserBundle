<?php

namespace Pintushi\Bundle\UserBundle\Repository;

use Pintushi\Bundle\UserBundle\Entity\User;
use Pintushi\Bundle\CoreBundle\Doctrine\ORM\EntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Pintushi\Bundle\UserBundle\Entity\UserOAuth;

class UserOAuthRepository extends EntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserOAuth::class);
    }
}
