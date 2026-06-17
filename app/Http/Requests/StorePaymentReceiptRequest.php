<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class StorePaymentReceiptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Set to true if authorization checks are handled via middleware gates.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; 
    }

    /**
     * Get the validation rules that apply to the incoming request payload.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'invoice_number'        => 'required|string|exists:invoices,invoice_number',
            'gross_amount_received' => 'required|numeric|min:0.01',
            'receipt_date'          => 'required|string',
            'payment_method'        => 'required|string|in:Transfer,Cash,Check,Mobile Money',
            'payment_ref'           => 'nullable|string|max:255',
            'reference_no'          => 'nullable|string|max:255',
            'description'           => 'nullable|string|max:1000',
            'insurer_name'          => 'nullable|string',
        ];
    }

    /**
     * Custom Attribute Names for Error Messages
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'invoice_number'        => 'Invoice Number',
            'gross_amount_received' => 'Amount Received',
            'receipt_date'          => 'Receipt Date',
            'payment_method'        => 'Payment Method',
        ];
    }

    /**
     * Parse formatting variations into a standard database-compliant YYYY-MM-DD string.
     * Handles: '19-May-2026', '19/05/2026', and fallback standard strings.
     *
     * @return string
     */
    public function getSanitizedDate(): string
    {
        $rawDate = trim($this->input('receipt_date'));

        try {
            // Case 1: Matches '19-May-2026' or '1-May-2026' (DD-Mmm-YYYY)
            if (preg_match('/^\d{1,2}-[A-Za-z]{3}-\d{4}$/', $rawDate)) {
                return Carbon::createFromFormat('d-M-Y', $rawDate)->format('Y-m-d');
            } 
            
            // Case 2: Matches '19/05/2026' (Standard forward-slash layout)
            if (str_contains($rawDate, '/')) {
                return Carbon::createFromFormat('d/m/Y', $rawDate)->format('Y-m-d');
            }

            // Case 3: Standard framework fallback parsing engine
            return Carbon::parse($rawDate)->format('Y-m-d');

        } catch (\Exception $e) {
            // Ultimate fallback to today's date if parsing fails completely
            logger()->warning("Could not parse receipt date: '{$rawDate}'. Defaulting to current system timestamp.");
            return now()->format('Y-m-d');
        }
    }
}
