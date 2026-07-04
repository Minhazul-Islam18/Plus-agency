<?php

namespace App\Http\Requests\Tender;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates a tender checkout submission.
 *
 * Buyer identity + agreement are required; the receipt (offline payment proof)
 * is MIME-checked because it is moved into the public assets directory — a
 * disguised script must never reach it. Optional buyer fields are length-bounded
 * to match their columns and to keep unvalidated input out of the database.
 */
class PurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Public checkout endpoint. Blacklist / duplicate-payment checks that
        // need custom messaging are handled in the controller.
        return true;
    }

    public function rules(): array
    {
        return [
            'tender_id'    => 'required|exists:tenders,id',
            'first_name'   => 'required|string|max:100',
            'last_name'    => 'required|string|max:100',
            'email'        => 'required|email|max:150',
            'phone_number' => 'required|string|max:30',
            'country'      => 'required|string|max:100',
            'gateway'      => 'required|string|max:100',
            'agree_terms'  => 'accepted',

            // Payment proof — the mimes rule checks the real MIME type, so a
            // script renamed to .jpg is rejected before it is stored publicly.
            'receipt'      => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',

            // Optional buyer / order fields, bounded to their storage columns.
            'city'                  => 'nullable|string|max:100',
            'company_name'          => 'nullable|string|max:200',
            'company_address'       => 'nullable|string|max:1000',
            'payment_reference'     => 'nullable|string|max:100',
            'gateway_type'          => 'nullable|string|max:50',
            'selected_module_ids'   => 'nullable|array',
            'selected_module_ids.*' => 'integer',
        ];
    }
}
