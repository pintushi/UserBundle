<?php

namespace Pintushi\Bundle\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

use Pintushi\Bundle\UserBundle\Entity\Group;

class GroupRepository extends EntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Group::class);
    }

    /**
     * Get user query builder
     *
     * @param  Group        $group
     * @return QueryBuilder
     */
    public function getUserQueryBuilder(Group $group)
    {
        return $this->_em->createQueryBuilder()
            ->select('u')
            ->from('PintushiUserBundle:User', 'u')
            ->join('u.groups', 'groups')
            ->where('groups = :group')
            ->setParameter('group', $group);
    }
}
