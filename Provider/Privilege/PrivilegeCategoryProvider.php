<?php

namespace Pintushi\Bundle\UserBundle\Provider\Privilege;

use Pintushi\Bundle\UserBundle\Model\PrivilegeCategory;

class PrivilegeCategoryProvider implements PrivilegeCategoryProviderInterface
{
    const NAME = 'platform';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getRolePrivilegeCategory()
    {
        $categoryList = [];
        $categoryList[] =
            new PrivilegeCategory(
                PrivilegeCategoryProviderInterface::DEFAULT_ACTION_CATEGORY,
                '账户',
                true,
                0
            );
        $categoryList[] =
            new PrivilegeCategory('application', '系统', false, 20);
        $categoryList[] = new PrivilegeCategory('entity', '实体', false, 40);

        return $categoryList;
    }
}
