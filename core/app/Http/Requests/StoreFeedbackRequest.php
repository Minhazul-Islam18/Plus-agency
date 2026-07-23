<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeedbackRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   *
   * @return bool
   */
  public function authorize()
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules()
  {
    return [
      'name' => 'required',
      'email' => 'required|email:rfc,dns',
      'subject' => 'required',
      'rating' => 'required|numeric',
      'feedback' => 'required'
    ];
  }

  /**
   * Get the error messages for the defined validation rules.
   *
   * @return array
   */
  public function messages()
  {
    return [
      'name.required'     => __('Name is required.'),
      'email.required'    => __('Email is required.'),
      'email.email'       => __('Please enter a valid email address.'),
      'subject.required'  => __('Subject is required.'),
      'rating.required'   => __('Please select a rating.'),
      'rating.numeric'    => __('Please select a valid rating.'),
      'feedback.required' => __('Feedback is required.'),
    ];
  }
}
