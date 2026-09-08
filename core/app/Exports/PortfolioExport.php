<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PortfolioExport implements FromCollection, WithHeadings, WithMapping
{
    public $portfolios;

    public function __construct($portfolios)
    {
        $this->portfolios = $portfolios;
    }

    public function collection()
    {
        return $this->portfolios;
    }

    public function map($portfolio): array
    {
        return [
            $portfolio->title,
            $portfolio->client_name,
            !empty($portfolio->sector) ? $portfolio->sector->name : '-',
            $portfolio->country,
            !empty($portfolio->statusInfo) ? $portfolio->statusInfo->name : '-',
            $portfolio->start_date,
            $portfolio->submission_date,
            $portfolio->is_published ? 'Published' : 'Unpublished',
        ];
    }

    public function headings(): array
    {
        return [
            'Title',
            'Client',
            'Sector',
            'Country',
            'Status',
            'Start Date',
            'End Date',
            'Visibility',
        ];
    }
}
