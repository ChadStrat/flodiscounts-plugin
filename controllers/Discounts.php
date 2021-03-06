<?php namespace Radiantweb\Flodiscounts\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Discounts Back-end Controller
 */
class Discounts extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Radiantweb.Flodiscounts', 'flodiscounts', 'discounts');
    }
}