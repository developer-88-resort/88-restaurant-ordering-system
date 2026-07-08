<?php

namespace App\Http\Controllers;

use App\Enums\TableStatus;
use App\Http\Requests\StoreBulkTablesRequest;
use App\Http\Requests\StoreTableRequest;
use App\Http\Requests\UpdateTableRequest;
use App\Models\RestaurantTable;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class TableController extends Controller
{
    public function index(): View
    {
        $tables = RestaurantTable::orderBy('table_number')->get();

        return view('tables.index', ['tables' => $tables]);
    }

    public function create(): View
    {
        return view('tables.create');
    }

    public function store(StoreTableRequest $request): RedirectResponse
    {
        RestaurantTable::create($request->validated());

        return redirect()->route('tables.index')
            ->with('status', 'Table created successfully.');
    }

    public function storeBulk(StoreBulkTablesRequest $request): RedirectResponse
    {
        $prefix = $request->string('prefix')->trim()->toString();
        $start = $request->integer('start');
        $count = $request->integer('count');

        $created = 0;
        $skipped = 0;

        for ($n = $start; $n < $start + $count; $n++) {
            $tableNumber = "{$prefix} {$n}";

            $table = RestaurantTable::firstOrCreate(['table_number' => $tableNumber]);

            $table->wasRecentlyCreated ? $created++ : $skipped++;
        }

        $message = "{$created} table(s) created.";
        if ($skipped > 0) {
            $message .= " {$skipped} already existed and were skipped.";
        }

        return redirect()->route('tables.index')->with('status', $message);
    }

    public function edit(RestaurantTable $table): View
    {
        return view('tables.edit', ['table' => $table]);
    }

    public function update(UpdateTableRequest $request, RestaurantTable $table): RedirectResponse
    {
        $table->update($request->validated());

        return redirect()->route('tables.index')
            ->with('status', 'Table updated successfully.');
    }

    public function updateStatus(Request $request, RestaurantTable $table): RedirectResponse
    {
        $request->validate([
            'status' => ['required', new Enum(TableStatus::class)],
        ]);

        $table->update(['status' => $request->string('status')->toString()]);

        return redirect()->back()
            ->with('status', "\"{$table->table_number}\" is now {$table->status->label()}.");
    }

    public function destroy(RestaurantTable $table): RedirectResponse
    {
        $table->delete();

        return redirect()->route('tables.index')
            ->with('status', 'Table deleted successfully.');
    }

    public function print(RestaurantTable $table): View
    {
        return view('tables.print', ['table' => $table]);
    }

    public function qrCode(Request $request, RestaurantTable $table): Response
    {
        $result = (new Builder(
            writer: new SvgWriter(),
            data: route('customer.tables.show', $table->qr_token),
            size: 300,
            margin: 10,
        ))->build();

        $headers = ['Content-Type' => $result->getMimeType()];

        if ($request->boolean('download')) {
            $headers['Content-Disposition'] = 'attachment; filename="table-'.$table->table_number.'-qr.svg"';
        }

        return response($result->getString(), 200, $headers);
    }
}
