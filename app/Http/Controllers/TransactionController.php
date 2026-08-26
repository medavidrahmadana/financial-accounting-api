<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with('coa.category')->orderBy('date', 'desc')->orderBy('id', 'desc');

        if ($request->has('year')) {
            $query->whereYear('date', $request->year);
        }

        $transactions = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'coa_id' => 'required|exists:chart_of_accounts,id',
            'description' => 'required|string|max:255',
            'debit' => 'nullable|numeric|min:0',
            'credit' => 'nullable|numeric|min:0'
        ]);

        $validated['debit'] = $validated['debit'] ?? 0;
        $validated['credit'] = $validated['credit'] ?? 0;

        $transaction = Transaction::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction recorded successfully',
            'data' => $transaction->load('coa.category')
        ], 201);
    }

    public function show($id)
    {
        $transaction = Transaction::with('coa.category')->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $transaction
        ]);
    }

    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        $validated = $request->validate([
            'date' => 'required|date',
            'coa_id' => 'required|exists:chart_of_accounts,id',
            'description' => 'required|string|max:255',
            'debit' => 'nullable|numeric|min:0',
            'credit' => 'nullable|numeric|min:0'
        ]);

        $validated['debit'] = $validated['debit'] ?? 0;
        $validated['credit'] = $validated['credit'] ?? 0;

        $transaction->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction updated successfully',
            'data' => $transaction->load('coa.category')
        ]);
    }

    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Transaction deleted successfully'
        ]);
    }
}
