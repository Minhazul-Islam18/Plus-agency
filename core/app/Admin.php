<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
  use Notifiable;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'role_id', 'username', 'email', 'password', 'first_name', 'last_name', 'image', 'status',
    'activation_token_hash', 'activation_expires_at',
    'temp_password', 'temp_password_expires_at', 'must_change_password',
    'failed_login_attempts', 'locked_at',
  ];


  public function role()
  {
    return $this->belongsTo('App\Role');
  }

  public function isSuperAdmin()
  {
    return $this->id == 1 && is_null($this->role_id);
  }
}
