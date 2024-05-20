<?php

namespace App\Http\Controllers\Api\SalesInvoice;

use App\Http\Controllers\Controller;
use App\Http\Resources\SalesInvoiceResource;
use App\Models\SalesInvoice\SalesInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SalesInvoiceController extends Controller
{
    // show all invoices
    public function index()
    {
        $salesInvoices = SalesInvoice::all();
        return new SalesInvoiceResource(true, "Success", $salesInvoices);
    }

    // create invoice
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_name' => 'required|string',
            'customer_name' => 'required|string',
            'sales_person' => 'required|string',
            'date' => 'required|date',
            'items' => 'required|array',
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => 'required|integer',
            'items.*.price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $invoiceNumber = $this->generateInvoiceNumber($validated['branch_name']);

        $salesInvoice = SalesInvoice::create([
            'invoice_number' => $invoiceNumber,
            'branch_name' => $validated['branch_name'],
            'customer_name' => $validated['customer_name'],
            'sales_person' => $validated['sales_person'],
            'date' => $validated['date'],
            'grand_total' => 0,
        ]);

        $grandTotal = 0;
        foreach ($validated['items'] as $item) {
            $total = $item['quantity'] * $item['price'];
            $grandTotal += $total;
            $salesInvoice->items()->create([
                'item_name' => $item['item_name'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'total' => $total,
            ]);
        }

        $salesInvoice->update(['grand_total' => $grandTotal]);

        return new SalesInvoiceResource(true, "Created", $salesInvoice->load('items'));
    }

    // show by id
    public function show($id)
    {
        $salesInvoice = SalesInvoice::with('items')->findOrFail($id);
        return new SalesInvoiceResource(true, "Success", $salesInvoice);
    }

    // update invoice
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'branch_name' => 'string',
            'customer_name' => 'string',
            'sales_person' => 'string',
            'date' => 'date',
            'items' => 'array',
            'items.*.item_name' => 'string',
            'items.*.quantity' => 'integer',
            'items.*.price' => 'numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $salesInvoice = SalesInvoice::findOrFail($id);
        $salesInvoice->update($request->only('branch_name', 'customer_name', 'sales_person', 'date'));

        if ($request->has('items')) {
            $salesInvoice->items()->delete();
            $grandTotal = 0;
            foreach ($validated['items'] as $item) {
                $total = $item['quantity'] * $item['price'];
                $grandTotal += $total;
                $salesInvoice->items()->create([
                    'item_name' => $item['item_name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $total,
                ]);
            }
            $salesInvoice->update(['grand_total' => $grandTotal]);
        }

        return new SalesInvoiceResource(true, "Success", $salesInvoice->load('items'));
    }

    // delete invoice
    public function destroy($id)
    {
        $salesInvoice = SalesInvoice::findOrFail($id);
        $salesInvoice->delete();
        return new SalesInvoiceResource(true, "Deleted", null);
    }

    // generate unique number for invoice
    private function generateInvoiceNumber($branchName)
    {
        $year = date('y');
        $latestInvoice = SalesInvoice::where('branch_name', $branchName)->orderBy('created_at', 'desc')->first();
        $number = $latestInvoice ? ((int)substr($latestInvoice->invoice_number, -4)) + 1 : 1;
        return sprintf("SI/%s/%02d/%04d", strtoupper($branchName), $year, $number);
    }
}
