<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Exports\ProfitLossExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function profitLoss(Request $request)
    {
        $year = $request->query('year', 2022);

        $categories = Category::all();
        $transactions = Transaction::with('coa.category')
            ->whereYear('date', $year)
            ->get();

        // Matrix structure [category_id][month_1_to_12]
        $matrix = [];
        foreach ($categories as $cat) {
            $matrix[$cat->id] = array_fill(1, 12, 0);
        }

        foreach ($transactions as $t) {
            if ($t->coa && $t->coa->category_id) {
                $catId = $t->coa->category_id;
                $month = (int) date('m', strtotime($t->date));
                // Income uses credit, expense uses debit
                $amount = ($t->coa->category->type === 'income') ? (float)$t->credit : (float)$t->debit;
                $matrix[$catId][$month] += $amount;
            }
        }

        // Separate Income & Expense
        $incomeData = [];
        $totalIncomeMonthly = array_fill(1, 12, 0);

        foreach ($categories->where('type', 'income') as $cat) {
            $amounts = [];
            for ($m = 1; $m <= 12; $m++) {
                $val = $matrix[$cat->id][$m];
                $amounts[sprintf('%d-%02d', $year, $m)] = $val;
                $totalIncomeMonthly[$m] += $val;
            }
            $incomeData[] = [
                'category_id' => $cat->id,
                'category_name' => $cat->name,
                'amounts' => $amounts
            ];
        }

        $expenseData = [];
        $totalExpenseMonthly = array_fill(1, 12, 0);

        foreach ($categories->where('type', 'expense') as $cat) {
            $amounts = [];
            for ($m = 1; $m <= 12; $m++) {
                $val = $matrix[$cat->id][$m];
                $amounts[sprintf('%d-%02d', $year, $m)] = $val;
                $totalExpenseMonthly[$m] += $val;
            }
            $expenseData[] = [
                'category_id' => $cat->id,
                'category_name' => $cat->name,
                'amounts' => $amounts
            ];
        }

        $totalIncomeAmounts = [];
        $totalExpenseAmounts = [];
        $netIncomeAmounts = [];

        for ($m = 1; $m <= 12; $m++) {
            $key = sprintf('%d-%02d', $year, $m);
            $totalIncomeAmounts[$key] = $totalIncomeMonthly[$m];
            $totalExpenseAmounts[$key] = $totalExpenseMonthly[$m];
            $netIncomeAmounts[$key] = $totalIncomeMonthly[$m] - $totalExpenseMonthly[$m];
        }

        return response()->json([
            'status' => 'success',
            'year' => (int)$year,
            'data' => [
                'income' => $incomeData,
                'total_income' => $totalIncomeAmounts,
                'expense' => $expenseData,
                'total_expense' => $totalExpenseAmounts,
                'net_income' => $netIncomeAmounts
            ]
        ]);
    }

    public function exportProfitLoss(Request $request)
    {
        $year = $request->query('year', 2022);
        return Excel::download(new ProfitLossExport($year), "Profit_Loss_Report_{$year}.xlsx");
    }
}
