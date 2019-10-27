<?php
/**
 * Created by PhpStorm.
 * User: chenrongrong
 * Date: 2019/9/21
 * Time: 2:47 PM
 */


namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OrderTemplateUser extends Model
{
    public $table = "order_template_user";
    protected $guarded = [];
    public $timestamps = false;
}