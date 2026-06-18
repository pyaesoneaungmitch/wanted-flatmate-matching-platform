<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicProfile extends Model {
  protected $table = 'public_profile';
  protected $primaryKey = 'user_id';
  public $incrementing = false;
  protected $fillable = ['user_id','display_name','age','bio','city','budget_min','budget_max'];
  public $timestamps = false; // because your table has updated_at only
}