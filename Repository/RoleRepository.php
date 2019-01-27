<?php
namespace Pintushi\Bundle\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Videni\Bundle\RestBundle\Doctrine\ORM\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pintushi\Bundle\OrganizationBundle\Entity\Organization;
use Pintushi\Bundle\UserBundle\Entity\Role;
use Pintushi\Bundle\UserBundle\Entity\User;

class RoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    /**
     * Returns a query builder which can be used to get a list of users assigned to the given role
     *
     * @param  Role $role
     * @return QueryBuilder
     */
    public function getUserQueryBuilder(Role $role)
    {
        return $this->_em->createQueryBuilder()
            ->select('u')
            ->from('PintushiUserBundle:User', 'u')
            ->join('u.roles', 'role')
            ->where('role = :role')
            ->setParameter('role', $role);
    }

    /**
     * Checks if there are at least one user assigned to the given role
     *
     * @param Role $role
     * @return bool
     */
    public function hasAssignedUsers(Role $role)
    {
        $findResult = $this->getUserQueryBuilder($role)
            ->select('role.id')
            ->setMaxResults(1)
            ->getQuery()
            ->getArrayResult();

        return !empty($findResult);
    }

    /**
     * @param Role $role
     * @return User
     */
    public function getFirstMatchedUser(Role $role)
    {
        return $this
            ->getUserQueryBuilder($role)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function createOrganizationQueryBuilder(Organization $organization)
    {
        $qb = $this->createQueryBuilder('o');

        if (!$organization->isGlobal()) {
            $qb->andWhere($qb->expr()->in('IDENTITY(o.organization)', [$organization->getId(), null]));
        }

        return $qb;
    }
}
