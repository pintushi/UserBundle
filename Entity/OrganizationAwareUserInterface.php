<?php

namespace Pintushi\Bundle\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Pintushi\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;


interface OrganizationAwareUserInterface extends OrganizationAwareInterface
{
    /**
     * Get User Organizations
     *
     * @param  bool $onlyActive Returns enabled organizations only
     * @return ArrayCollection|OrganizationInterface[]
     */
    public function getOrganizations($onlyActive = false);
}
