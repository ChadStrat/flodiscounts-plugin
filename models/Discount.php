<?php namespace Radiantweb\Flodiscounts\Models;

use Model;
use Radiantweb\Flodiscounts\Models\Discounts;
/**
 * Discount Model
 */
class Discount extends Model
{
    use \October\Rain\Database\Traits\Purgeable;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'radiantweb_flodiscounts_discount';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Validation rules
     */
    public $rules = [];

    /**
     * @var array puge rules
     */
    public $purgeable = ['discount'];

    /**
     * @var array Relations
     */
    public $hasOne = ['discounts' => ['Radiantweb\Flodiscounts\Models\Discounts']];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    public function afterSave()
    {
        Discounts::where('discount_id',null)->update(array('discount_id'=>$this->id));
    }

}