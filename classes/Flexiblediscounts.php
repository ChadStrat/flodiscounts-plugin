<?php namespace Radiantweb\Flodiscounts\Classes;

use Radiantweb\Flocommerce\Models\Cart as FlocommerceCart;
use Radiantweb\Flocommerce\Models\Product as FlocommerceProduct;
use Radiantweb\Flocommerce\Models\DiscountsApplied as FlocommerceAppliedDiscounts;
use Radiantweb\Flodiscounts\Models\Discount as flodiscountsDiscount;
use Session;
use DB;
/**
 * Suply shipping form & cost
 * Requires min php 5.3  
 *
 * REQUIRED METHODS:
 *
 * - getDiscountByID($id(string),$items(array))
 *      ** fetches code saved to radiantweb_flo_discounts_applied by ID
 *      ** used by Radiantweb\Flocommerce\Models\Cart
 * - checkDiscountCode($code(string),$discount_parent(string)) /
 *      ** checks for any matching code/discount and creates
 *          a cart item to radiantweb_flo_discounts_applied
 *      ** used by Radiantweb\Flocommerce\Components\Checkout & Radiantweb\Flocommerce\Models\Cart
 * - getDiscounts($items(array)) //checks all items in a cart to all possible discounts
 *      ** dynamically applied discounts for automatic item & cart discounts
 *      ** used by Radiantweb\Flocommerce\Models\Cart
 *
 * @package radiantweb/flo
 * @author ChadStrat
 */
class Flexiblediscounts
{

    public $cart_discounts = array();

    public function __construct($type=null)
    {

    }

    public static function getDiscountByID($id,$items)
    {
        $discount = flodiscountsDiscount::where('id',$id)->first();
        if($discount->discounts->mod_target == 'amount'){
            return array('discount'=> $discount->discounts->mod_value,'description'=>$discount->description);
        }else{

            $total = 0;

            foreach($items as $item)
            {
                /* 
                 * if there is a product_id present then
                 * we know this is a per-item discount 
                 * else this is a cart discount
                 */
                if($discount->discounts->product_id && $discount->discounts->product_id == $item['product_id']){
                    $total += $item['price'];
                }else{
                    $total += $item['price'];
                }
            }

            if($discount->discounts->mod_target == 'amount'){
                $total = floatval($discount->discounts->mod_value);
            }else{
                $total = floatval($total) * (floatval($discount->discounts->mod_value)/100);
            }

            return array('discount'=> (-1 * abs($total)),'description'=>$discount->description);
        }
    }

    /*
     * @params 
     * - $code = discount code to check
     * - $session = cartID
     * adds a line_item to the cart
     */
    public static function checkDiscountCode($code,$discount_parent)
    {   

        $session = Session::get('flo_cart');

        $discounts = flodiscountsDiscount::get();

        $cart_discounts = array();

        foreach($discounts as $discount)
        {
            /* only check code discount types */
            if( $discount->discounts->application == 'code' )
            {
        
                /* check if code matches */
                if($code == $discount->discounts->code)
                {

                    /* is this code available? */
                    if( $discount->discounts->code_limit > 0 &&
                        $discount->discounts->code_qty < 1
                      ){
                            return array('error'=>'Sorry...this code is limited qty and has been used up!');
                       }

                    /* check for existing cart item */
                    $cart = new FlocommerceCart();
                    $cart = $cart->buildCart($session);

                    $total = 0;
                    $item_count = 0;
                    $discounted_item = null;
                    $free_item_count = 0;

                    /* @array $cart['cart_items'] */
                    foreach($cart['cart_items'] as $item)
                    {
                        /* 
                         * if there is a product_id present then
                         * we know this is a per-item discount 
                         * else this is a cart discount
                         */
                        if($discount->discounts->product_id && $discount->discounts->product_id == $item['product_id']){
                            $total += $item['price'];
                            $item_count += $item['qty'];

                            if(strpos($item['extra'], $discount->name) > 0){
                                $free_item_count++;
                                $discounted_item = $item;
                             }
                        }else{
                            $total += $item['price'];
                            $item_count += $item['qty'];
                        }
                    }

                    /* we need to verify that the discount indeed applies */
                    if($item_count > 0){
                        if($discount->discounts->comp_type == 'qty')
                        {
                            $discounted = Flexiblediscounts::functOporator($discount->discounts->comp_oporator,$item_count,$discount->discounts->comp_value);
                        }else{
                            $discounted = Flexiblediscounts::functOporator($discount->discounts->comp_oporator,$total,$discount->discounts->comp_value);
                        }
                        if($discounted)
                        {
                            if($discount->discounts->mod_target == 'amount'){
                                $cart_discounts[] = array('discount'=> $discount->discounts->mod_value,'description'=>$discount->description);
                            }elseif($discount->discounts->mod_target == 'item'){
                                /*
                                 * if our item count is lower than our comparitor value
                                 * we add another of this item to the cart
                                 */
                                if($free_item_count < $discount->discounts->mod_value && $item_count >= $discount->discounts->comp_value){
                                    $model = FlocommerceCart::where('id',$item['cart_id'])->first();

                                    $data = $model->attributes;

                                    for($ci=$free_item_count;$ci < $discount->discounts->mod_value; $ci++){
                                        $new_model = new FlocommerceCart;
                                    
                                        $new_model->session = $model->session;
                                        $new_model->product_id = $model->product_id;
                                        $new_model->qty = $model->qty;
                                        $new_model->extra = ' - '.$discount->name;
                                        $new_model->options = $model->options;
                                        $new_model->paid_options = $model->paid_options;
                                        $new_model->price_adjusted = 0;
            
                                        $new_model->save();
                                    }
                                }

                                if($item_count < $discount->discounts->comp_value){
                                    $extra = $discount->name;
                                    $model = FlocommerceCart::where('id',$item['cart_id'])
                                                            ->where('extra','like',"%$extra%")
                                                            ->first();
                                    $model->delete();
                                }

                                return true;
                            }else{
                                $total = floatval($total) * (floatval($discount->discounts->mod_value)/100);
                                $cart_discounts[] = array('discount'=> $total,'description'=>$discount->description);
                            }
                        }
                    }

                    /* check if discount has already been utilized for this cart */
                    $used_discount = FlocommerceAppliedDiscounts::where('session',$session)
                                                               ->where('discount_parent',$discount_parent)
                                                               ->where('applied_discount',$discount->id)
                                                               ->first();
                    
                    /* 
                     * if it's a valid discount and 
                     * one does not exist in 
                     * this cart, apply it.
                     */
                    if(count($cart_discounts) > 0 && !$used_discount){
                        $data = array(
                            'session'=>$session,
                            'discount_parent'=>$discount_parent,
                            'applied_discount'=> $discount->id,
                        );
                        //\Log::info($data); 

                        /* apply discount */
                        FlocommerceAppliedDiscounts::insert($data);

                        /* update discount qty */
                        if($discount->discounts->code_limit > 0){
                            $dqty = $discount->discounts->code_qty - 1;
                            DB::table('radiantweb_flodiscounts_discounts')->where('id',$discount->discounts->id)->update(array('code_qty'=>$dqty));
                        }
                        return true;
                    }
                }
            }
        }
        return array('error'=>'Sorry...no discounts were found matching this code!');
    }

    public static function getDiscounts($items)
    {
        $cl = new Flexiblediscounts();
        $cl->getCartDiscounts($items);

        return $cl->cart_discounts;
    }
    
    /*
     * @params 
     * - $params = form inputs defined above
     * - $session = cartID
     * - $items = cart items via $session
     * calculates discounts
     */
    public function getCartDiscounts($items)
    {
        $cart_discounts = $this->cart_discounts;

        $discounts = flodiscountsDiscount::get();

        foreach($discounts as $discount)
        {
            if( $discount->discounts->application == 'auto' &&
                ($discount->discounts->expires < 1 || $discount->discounts->expire_date < date('Y-m-d'))
              )
            {
                $total = 0;
                $item_count = 0;
                $free_item_count = 0;
                $discounted_item = null;
                foreach($items as $item)
                {   
                    if($discount->discounts->product_id && $discount->discounts->product_id == $item['product_id']){
                        $total += $item['price'];
                        $item_count += $item['qty'];

                         if(strpos($item['extra'], $discount->name) > 0){
                            $free_item_count++;
                            $discounted_item = $item;
                         }

                    }else{
                        $total += $item['price'];
                        $item_count += $item['qty'];
                    }
                }

                if($item_count > 0){
                    if($discount->discounts->comp_type == 'qty')
                    {
                        $discounted = Flexiblediscounts::functOporator($discount->discounts->comp_oporator,$item_count,$discount->discounts->comp_value);
                    }else{
                        $discounted = Flexiblediscounts::functOporator($discount->discounts->comp_oporator,$total,$discount->discounts->comp_value);
                    }

                    if($discounted)
                    {
                        if($discount->discounts->mod_target == 'amount'){
                            $cart_discounts[] = array('discount'=> $discount->discounts->mod_value,'description'=>$discount->description);
                        }elseif($discount->discounts->mod_target == 'item'){
                            /*
                             * if our item count is lower than our comparitor value
                             * we add another of this item to the cart
                             */
                            if($free_item_count < $discount->discounts->mod_value && $item_count >= $discount->discounts->comp_value){
                                $model = FlocommerceCart::where('id',$item['cart_id'])->first();

                                $data = $model->attributes;

                                for($ci=$free_item_count;$ci < $discount->discounts->mod_value; $ci++){
                                    $new_model = new FlocommerceCart;
                                
                                    $new_model->session = $model->session;
                                    $new_model->product_id = $model->product_id;
                                    $new_model->qty = $model->qty;
                                    $new_model->extra = ' - '.$discount->name;
                                    $new_model->options = $model->options;
                                    $new_model->paid_options = $model->paid_options;
                                    $new_model->price_adjusted = 0;
        
                                    $new_model->save();
                                }
                            }

                            if($item_count < $discount->discounts->comp_value){
                                $extra = $discount->name;
                                $model = FlocommerceCart::where('id',$item['cart_id'])
                                                        ->where('extra','like',"%$extra%")
                                                        ->first();
                                $model->delete();
                            }

                        }else{
                            $total = floatval($total) * (floatval($discount->discounts->mod_value)/100);
                            $cart_discounts[] = array('discount'=> $total,'description'=>$discount->description);
                        }
                    }
                }
            }
        }
        $this->cart_discounts = $cart_discounts;
    }



    public static function functOporator($opporator,$val1,$val2)
    {
        switch($opporator){
            case '==':
                if($val1 == $val2)
                    return true;
                break;
            case '>=':

                if($val1 >= $val2)
                    return true;
                break;
            case '<=':
                if($val1 <= $val2)
                    return true;
                break;
            case '>':
                if($val1 > $val2)
                    return true;
                break;
            case '<':
                if($val1 < $val2)
                    return true;
                break;
            case 'IS NULL':
                if(is_null($val1))
                    return true;
                break;
            case 'IS NOT NULL':
                if(!is_null($val1))
                    return true;
                break;
        }
        return false;
    }
}
