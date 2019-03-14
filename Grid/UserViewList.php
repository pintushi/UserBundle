<?php

namespace Pintushi\Bundle\UserBundle\Grid;

use Pintushi\Bundle\GridBundle\Entity\GridView;
use Pintushi\Bundle\GridBundle\Extension\GridViews\AbstractViewsList;
use Pintushi\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Pintushi\Bundle\FilterBundle\Form\Type\Filter\EnumFilterType;
use Pintushi\Bundle\FilterBundle\Form\Type\Filter\AbstractDateFilterType;

class UserViewList extends AbstractViewsList
{
    const GRID_NAME = 'user-grid';

    private $localeSettings;

    protected $systemViews = [
        'user.active' => [
            'name' => 'user.active',
            'label' => 'pintushi.user.datagrid.views.active',
            'is_default' => true,
            'grid_name' => self::GRID_NAME,
            'type' => GridView::TYPE_PUBLIC,
            'icon' => 'fa-check',
            'filters' => [
                'enabled' => [
                    'value' => BooleanFilterType::TYPE_YES,
                ],
            ],
            'sorters' => [],
            'columns' => [
                'enabled' => [
                    'renderable' => false,
                ],
            ],
        ],
        'user.disabled' => [
            'name' => 'user.disabled',
            'label' => 'pintushi.user.datagrid.views.disabled',
            'is_default' => false,
            'grid_name' => self::GRID_NAME,
            'type' => GridView::TYPE_PUBLIC,
            'icon' => 'fa-ban',
            'filters' => [
                'enabled' => [
                    'value' => BooleanFilterType::TYPE_NO,
                ],
            ],
            'sorters' => [],
            'columns' => [
                'enabled' => [
                    'renderable' => false,
                ],
            ],
        ],
    ];

    /**
     * {@inheritDoc}
     */
    protected function getViewsList()
    {
        return $this->getSystemViewsList();
    }

    /**
     * {@inheritdoc}
     */
    public function getSystemViewsList()
    {
        $views = parent::getSystemViewsList();

        foreach ($views as $view) {
            $name = $view->getName();
            if (!empty($this->systemViews[$name]['icon'])) {
                $view->setIcon($this->systemViews[$name]['icon']);
            }
        }

        return $views;
    }
}
