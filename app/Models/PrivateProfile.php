<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivateProfile extends Model {
  protected $table = 'private_profile';
  protected $primaryKey = 'user_id';
  public $incrementing = false;
  protected $fillable = ['user_id'];
  public $timestamps = false;
}