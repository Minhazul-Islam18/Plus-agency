<?php

namespace App\Exports;

use App\BasicExtra;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TenderEnrollExport implements FromCollection, WithHeadings, WithMapping
{
    public $enrolls;

    public function __construct($enrolls)
    {
        $this->enrolls = $enrolls;
    }

    public function collection()
    {
        return $this->enrolls;
    }

    public function map($enroll): array
    {
        $bex = BasicExtra::firstOrFail();
        $sym = $bex->base_currency_symbol_position == 'left'
            ? $bex->base_currency_symbol
            : '';
        $symR = $bex->base_currency_symbol_position == 'right'
            ? $bex->base_currency_symbol
            : '';

        return [
            $enroll->order_number,
            $enroll->first_name . ' ' . $enroll->last_name,
            $enroll->email,
            !empty($enroll->tender) ? $enroll->tender->title : '-',
            $sym . number_format($enroll->technical_proposal_fee, 2) . $symR,
            $sym . number_format($enroll->financial_proposal_fee, 2) . $symR,
            $enroll->payment_method,
            $enroll->payment_status,
            $enroll->created_at,
        ];
    }

    public function headings(): array
    {
        return [
            'Order Number',
            'Name',
            'Email',
            'Tender',
            'Technical Proposal Fee',
            'Financial Proposal Fee',
            'Gateway',
            'Payment Status',
            'Date',
        ];
    }
}
