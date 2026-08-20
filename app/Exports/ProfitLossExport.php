<?php

namespace App\Exports;

use App\Models\Category;
use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProfitLossExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $year;

    public function __construct($year = 2022)
    {
        $this->year = $year;
    }

    public function collection()
    {
        $categories = Category::all();
        $transactions = Transaction::with('coa.category')
            ->whereYear('date', $this->year)
            ->get();

        $monthlyData = [];
        foreach ($categories as $cat) {
            $monthlyData[$cat->id] = array_fill(1, 12, 0);
        }

        foreach ($transactions as $t) {
            if ($t->coa && $t->coa->category_id) {
                $catId = $t->coa->category_id;
                $month = (int) date('m', strtotime($t->date));
                $amount = ($t->coa->category->type === 'income') ? $t->credit : $t->debit;
                $monthlyData[$catId][$month] += $amount;
            }
        }

        $rows = collect();

        // Income Section
        $incomeCategories = $categories->where('type', 'income');
        $totalIncomeMonthly = array_fill(1, 12, 0);

        foreach ($incomeCategories as $cat) {
            $row = [$cat->name];
            for ($m = 1; $m <= 12; $m++) {
                $val = $monthlyData[$cat->id][$m];
                $row[] = $val;
                $totalIncomeMonthly[$m] += $val;
            }
            $rows->push($row);
        }

        // Total Income Row
        $totalIncomeRow = ['Total Income'];
        for ($m = 1; $m <= 12; $m++) {
            $totalIncomeRow[] = $totalIncomeMonthly[$m];
        }
        $rows->push($totalIncomeRow);

        // Expense Section
        $expenseCategories = $categories->where('type', 'expense');
        $totalExpenseMonthly = array_fill(1, 12, 0);

        foreach ($expenseCategories as $cat) {
            $row = [$cat->name];
            for ($m = 1; $m <= 12; $m++) {
                $val = $monthlyData[$cat->id][$m];
                $row[] = $val;
                $totalExpenseMonthly[$m] += $val;
            }
            $rows->push($row);
        }

        // Total Expense Row
        $totalExpenseRow = ['Total Expense'];
        for ($m = 1; $m <= 12; $m++) {
            $totalExpenseRow[] = $totalExpenseMonthly[$m];
        }
        $rows->push($totalExpenseRow);

        // Net Income Row
        $netIncomeRow = ['Net Income'];
        for ($m = 1; $m <= 12; $m++) {
            $netIncomeRow[] = $totalIncomeMonthly[$m] - $totalExpenseMonthly[$m];
        }
        $rows->push($netIncomeRow);

        return $rows;
    }

    public function headings(): array
    {
        $headers = ['Category'];
        for ($m = 1; $m <= 12; $m++) {
            $headers[] = sprintf('%d-%02d', $this->year, $m);
        }
        return $headers;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0F172A']
                ]
            ],
        ];
    }
}
