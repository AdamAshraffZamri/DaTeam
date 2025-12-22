<?php

namespace App\Http\Controllers;

use App\Models\Loyalty;
use App\Models\Customer;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{

    public function index()
    {
        $loyalties = Loyalty::with('customer')->get();
        return view('loyalty.index', compact('loyalties'));
    }

    public function create()
    {
        $customers = Customer::all();
        return view('loyalty.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'points' => 'required|integer',
            'description' => 'required|string',
            'date_awarded' => 'required|date',
        ]);

        Loyalty::create($request->all());
        return redirect()->route('loyalty.index')->with('success', 'Reward added successfully.');
    }

    public function edit(Loyalty $loyalty)
    {
        $customers = Customer::all();
        return view('loyalty.edit', compact('loyalty', 'customers'));
    }

    public function update(Request $request, Loyalty $loyalty)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'points' => 'required|integer',
            'description' => 'required|string',
            'date_awarded' => 'required|date',
        ]);

        $loyalty->update($request->all());
        return redirect()->route('loyalty.index')->with('success', 'Reward updated successfully.');
    }

    public function destroy(Loyalty $loyalty)
    {
        $loyalty->delete();
        return redirect()->route('loyalty.index')->with('success', 'Reward deleted successfully.');
    }

}