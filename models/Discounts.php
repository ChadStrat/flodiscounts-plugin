<?php namespace Radiantweb\Flodiscounts\Models;

use Model;

/**
 * Discounts Model
 */
class Discounts extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'radiantweb_flodiscounts_discounts';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'type',
        'application',
        'expires',
        'expire_date',
        'product_id',
        'comp_target',
        'comp_type',
        'comp_oporator',
        'comp_value',
        'mod_value',
        'mod_type',
        'mod_target',
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

}