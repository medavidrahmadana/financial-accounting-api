<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Categories (Master Kategori COA)
        $salaryCategory = Category::create(['name' => 'Salary', 'type' => 'income']);
        $otherIncomeCategory = Category::create(['name' => 'Other Income', 'type' => 'income']);
        $familyExpenseCategory = Category::create(['name' => 'Family Expense', 'type' => 'expense']);
        $transportExpenseCategory = Category::create(['name' => 'Transport Expense', 'type' => 'expense']);
        $mealExpenseCategory = Category::create(['name' => 'Meal Expense', 'type' => 'expense']);

        // 2. Seed Master Chart of Account (COA)
        $gajiKaryawan = ChartOfAccount::create(['code' => 401, 'name' => 'Gaji Karyawan', 'category_id' => $salaryCategory->id]);
        $gajiKetuaMPR = ChartOfAccount::create(['code' => 402, 'name' => 'Gaji Ketua MPR', 'category_id' => $salaryCategory->id]);
        $profitTrading = ChartOfAccount::create(['code' => 403, 'name' => 'Profit Trading', 'category_id' => $otherIncomeCategory->id]);
        
        $biayaSekolah = ChartOfAccount::create(['code' => 601, 'name' => 'Biaya Sekolah', 'category_id' => $familyExpenseCategory->id]);
        $bensin = ChartOfAccount::create(['code' => 602, 'name' => 'Bensin', 'category_id' => $transportExpenseCategory->id]);
        $parkir = ChartOfAccount::create(['code' => 603, 'name' => 'Parkir', 'category_id' => $transportExpenseCategory->id]);
        $makanSiang = ChartOfAccount::create(['code' => 604, 'name' => 'Makan Siang', 'category_id' => $mealExpenseCategory->id]);
        $makananPokok = ChartOfAccount::create(['code' => 605, 'name' => 'Makan Pokok Bulanan', 'category_id' => $mealExpenseCategory->id]);

        // 3. Seed Sample Transactions (Sesuai Gambar Excel)
        Transaction::create([
            'date' => '2022-01-01',
            'coa_id' => $gajiKaryawan->id,
            'description' => 'Gaji Di Perusahaan A',
            'debit' => 0,
            'credit' => 5000000
        ]);

        Transaction::create([
            'date' => '2022-01-02',
            'coa_id' => $gajiKetuaMPR->id,
            'description' => 'Gaji Ketum',
            'debit' => 0,
            'credit' => 7000000
        ]);

        Transaction::create([
            'date' => '2022-01-10',
            'coa_id' => $bensin->id,
            'description' => 'Bensin Anak',
            'debit' => 25000,
            'credit' => 0
        ]);
    }
}
