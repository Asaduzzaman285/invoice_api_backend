<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankController extends Controller
{
    // Create
    public function store(Request $request) {
        DB::insert('INSERT INTO bank (bank_name) VALUES (?)', [$request->bank_name]);
        return response()->json(['message' => 'Bank created successfully']);
    }

    // Read all
    public function index() {
        $banks = DB::select('SELECT * FROM bank');
        return response()->json($banks);
    }

    // Read single
    public function show($id) {
        $bank = DB::select('SELECT * FROM bank WHERE id = ?', [$id]);
        return response()->json($bank);
    }

    // Update
    public function update(Request $request, $id) {
        DB::update('UPDATE bank SET bank_name = ? WHERE id = ?', [$request->bank_name, $id]);
        return response()->json(['message' => 'Bank updated successfully']);
    }

    // Delete
    public function destroy($id) {
        DB::delete('DELETE FROM bank WHERE id = ?', [$id]);
        return response()->json(['message' => 'Bank deleted successfully']);
    }
}
