<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use Illuminate\Http\Request;

class ChartOfAccountController extends Controller
{
    public function index()
    {
        $coas = ChartOfAccount::with('category')->get();
        return response()->json([
            'status' => 'success',
            'data' => $coas
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|integer|unique:chart_of_accounts,code',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id'
        ]);

        $coa = ChartOfAccount::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Chart of Account created successfully',
            'data' => $coa->load('category')
        ], 201);
    }

    public function show($id)
    {
        $coa = ChartOfAccount::with('category')->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $coa
        ]);
    }

    public function update(Request $request, $id)
    {
        $coa = ChartOfAccount::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|integer|unique:chart_of_accounts,code,' . $coa->id,
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id'
        ]);

        $coa->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Chart of Account updated successfully',
            'data' => $coa->load('category')
        ]);
    }

    public function destroy($id)
    {
        $coa = ChartOfAccount::findOrFail($id);
        $coa->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Chart of Account deleted successfully'
        ]);
    }
}
