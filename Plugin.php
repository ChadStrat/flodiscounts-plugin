<?php namespace Radiantweb\Flodiscounts;

use App;
use Backend;
use System\Classes\PluginBase;
use Backend\Models\User as BackendUserModel;
use Cms\Classes\Theme;
use Cms\Classes\Partial;
use Twig\Lexer;

/**
 * FlexibleDiscounts Plugin Information File
 */
class Plugin extends PluginBase
{

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'FloDiscounts',
            'description' => 'Flexible Discounts for FloCommerce',
            'author'      => 'Radiantweb',
            'icon'        => 'icon-leaf'
        ];
    }


    public function registerNavigation()
    {
        return [
            'flodiscounts' => [
                'label'       => 'FloDiscounts',
                'url'         => Backend::url('radiantweb/flodiscounts/discounts'),
                'icon'        => 'icon-tag',
                'permissions' => ['flodiscounts:*'],
                'order'       => 500,

                'sideMenu' => [
                    'discounts' => [
                        'label'       => 'Discounts',
                        'icon'        => 'icon-tag',
                        'url'         => Backend::url('radiantweb/flodiscounts/discounts'),
                        'permissions' => ['flodiscounts:access_discounts'],
                    ],
                ]
            ]
        ];
    }

    public function registerFormWidgets()
    {
        return [
            'Radiantweb\Flodiscounts\Modules\Backend\Formwidgets\Discountbuilder' => [
                'label' => 'Discount Builder',
                'alias' => 'discountbuilder'
            ],
        ];
    }


    public function registerFloDiscountTypes()
    {
        $types = [
            'FloDiscounts' => 'Radiantweb\Flodiscounts\Classes\Flexiblediscounts',
        ];

        return $types;
    }
}
