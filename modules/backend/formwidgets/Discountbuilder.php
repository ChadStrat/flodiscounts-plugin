<?php namespace Radiantweb\Flodiscounts\Modules\Backend\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Radiantweb\Flocommerce\Models\Product;
use Radiantweb\Flodiscounts\Models\Discounts as FlodiscountsDiscount;
use DB;

/**
 * Price
 * Renders a code editor field.
 *
 * @package radiantweb\backend
 * @author ChadStrat
 */
class Discountbuilder extends FormWidgetBase
{
    /**
     * {@inheritDoc}
     */
    public $defaultAlias = 'discountbuilder';


    /**
     * {@inheritDoc}
     */
    public function init()
    {

    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('discountbuilder');
    }

    /**
     * Prepares the list data
     */
    public function prepareVars()
    {   
        $this->vars['products'] = Product::get();
        $this->vars['date'] = date('Y-m-d');
        $this->vars['value'] = $this->model->{$this->columnName} ? $this->model->{$this->columnName} : 0;
        $this->vars['name'] = $this->formField->getName();
        $this->vars['discount'] = $this->model->discounts;
        $this->vars['type'] = ! empty($this->model->discounts->type) ? $this->model->discounts->type : NULL;
        $this->vars['application'] = ! empty($this->model->discounts->application) ? $this->model->discounts->application : NULL;
        $this->vars['expires'] = ! empty($this->model->discounts->expires) ? $this->model->discounts->expires : NULL;
        $this->vars['expire_date'] = ! empty($this->model->discounts->expire_date) ? $this->model->discounts->expire_date : NULL;
        $this->vars['code'] = ! empty($this->model->discounts->code) ? $this->model->discounts->code : NULL;
        $this->vars['code_qty'] = ! empty($this->model->discounts->code_qty) ? $this->model->discounts->code_qty : 0;
        $this->vars['code_limit'] = ! empty($this->model->discounts->code_limit) ? $this->model->discounts->code_limit : NULL;
        $this->vars['product_id'] = ! empty($this->model->discounts->product_id) ? $this->model->discounts->product_id : NULL;
        $this->vars['comp_target'] = ! empty($this->model->discounts->comp_target) ? $this->model->discounts->comp_target : NULL;
        $this->vars['comp_type'] = ! empty($this->model->discounts->comp_type) ? $this->model->discounts->comp_type : NULL;
        $this->vars['comp_oporator'] = ! empty($this->model->discounts->comp_oporator) ? $this->model->discounts->comp_oporator : NULL;
        $this->vars['comp_value'] = ! empty($this->model->discounts->comp_value) ? $this->model->discounts->comp_value : NULL;
        $this->vars['mod_value'] = ! empty($this->model->discounts->mod_value) ? $this->model->discounts->mod_value : NULL;
        $this->vars['mod_type'] = ! empty($this->model->discounts->mod_type) ? $this->model->discounts->mod_type : NULL;
        $this->vars['mod_target'] = ! empty($this->model->discounts->mod_target) ? $this->model->discounts->mod_target : NULL;
    }

    /**
     * {@inheritDoc}
     */
    public function loadAssets()
    {
        $this->addCss('css/discount_builder.css');
    }
    
    /** 
    * Process the postback data for this widget. 
    * @param $value The existing value for this widget. 
    * @return string The new value for this widget. 
    */ 

    public function getSaveData($value) 
    { 
        //\Log::info(json_encode($value['application'][0])); 
        if($value['application'][0] != ''){
            $data = array(
                'type'=>! empty($value['type'][0]) ? $value['type'][0] : NULL,
                'application'=>! empty($value['application'][0]) ? $value['application'][0] : NULL,
                'expires'=>! empty($value['expires'][0]) ? $value['expires'][0] : NULL,
                'expire_date'=>! empty($value['expire_date'][0]) ? $value['expire_date'][0] : NULL,
                'code'=>! empty($value['code'][0]) ? $value['code'][0] : NULL,
                'code_qty'=>! empty($value['code_qty'][0]) ? $value['code_qty'][0] : 0,
                'code_limit'=>! empty($value['code_limit'][0]) ? $value['code_limit'][0] : NULL,
                'product_id'=>! empty($value['product_id'][0]) ? $value['product_id'][0] : NULL,
                'comp_target'=>! empty($value['comp_target'][0]) ? $value['comp_target'][0] : NULL,
                'comp_type'=>! empty($value['comp_type'][0]) ? $value['comp_type'][0] : NULL,
                'comp_oporator'=>! empty($value['comp_oporator'][0]) ? $value['comp_oporator'][0] : NULL,
                'comp_value'=>! empty($value['comp_value'][0]) ? $value['comp_value'][0] : NULL,
                'mod_value'=>! empty($value['mod_value'][0]) ? $value['mod_value'][0] : NULL,
                'mod_type'=>! empty($value['mod_type'][0]) ? $value['mod_type'][0] : NULL,
                'mod_target'=>! empty($value['mod_target'][0]) ? $value['mod_target'][0] : NULL,
            );
            if(! empty($value['discounts_id'][0])){
                FlodiscountsDiscount::where('id',$value['discounts_id'][0])->update($data);
            }else{
                $data['discount_id'] = $this->model->id;
                FlodiscountsDiscount::insert($data);
            }
        }
        //return $value; 
    } 

    public function onLoadTypeSelection()
    {
        $this->prepareVars();
        $this->vars['item_types'] = [];
        return $this->makePartial($_REQUEST['type']);
    }
}