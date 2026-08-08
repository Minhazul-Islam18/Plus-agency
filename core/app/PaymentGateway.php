<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
  protected $fillable = ['id', 'title', 'details', 'subtitle', 'name', 'type', 'information', 'keyword', 'status'];
  public $timestamps = false;

  public function convertAutoData()
  {
    return json_decode($this->information, true);
  }

  public function getAutoDataText()
  {
    $text = $this->convertAutoData();
    return end($text);
  }

  public function showKeyword()
  {
    $data = $this->keyword == null ? 'other' : $this->keyword;
    return $data;
  }

  public function showForm()
  {
    $show = '';
    $data = $this->keyword == null ? 'other' : $this->keyword;
    $values = ['paypal', 'moneroo'];
    if (in_array($data, $values)) {
      $show = 'no';
    } else {
      $show = 'yes';
    }
    return $show;
  }
}
