<?php

namespace App\Http\Controllers;

use App\Enums\AuditActionType;
use App\Enums\ChargeCategory;
use App\Http\Requests\StoreBillableServiceRequest;
use App\Http\Requests\UpdateBillableServiceRequest;
use App\Models\BillableService;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillableServiceController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $category = $request->string('category')->toString();
        $status = $request->string('status')->toString();

        $services = BillableService::query()
            ->when($search !== '', fn ($query) => $query->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($search).'%']))
            ->when($category !== '', fn ($query) => $query->where('category', $category))
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('billable-services.index', [
            'services' => $services,
            'search' => $search,
            'category' => $category,
            'status' => $status,
            'categories' => ChargeCategory::cases(),
        ]);
    }

    public function create(): View
    {
        return view('billable-services.create', [
            'categories' => ChargeCategory::cases(),
        ]);
    }

    public function store(StoreBillableServiceRequest $request): RedirectResponse
    {
        $service = BillableService::query()->create([
            'name' => $request->input('name'),
            'category' => $request->input('category'),
            'price' => $request->input('price'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogger::log(
            AuditActionType::BillableServiceCreated,
            "Added service \"{$service->name}\" at K {$service->price}.",
            $service,
            [
                'name' => $service->name,
                'category' => $service->category->value,
                'price' => (float) $service->price,
                'is_active' => $service->is_active,
            ],
        );

        return redirect()
            ->route('billable-services.index')
            ->with('success', 'Service added to the catalogue.');
    }

    public function edit(BillableService $billableService): View
    {
        return view('billable-services.edit', [
            'service' => $billableService,
            'categories' => ChargeCategory::cases(),
        ]);
    }

    public function update(UpdateBillableServiceRequest $request, BillableService $billableService): RedirectResponse
    {
        $billableService->update([
            'name' => $request->input('name'),
            'category' => $request->input('category'),
            'price' => $request->input('price'),
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditLogger::log(
            AuditActionType::BillableServiceUpdated,
            "Updated service \"{$billableService->name}\" (K {$billableService->price}).",
            $billableService,
            [
                'name' => $billableService->name,
                'category' => $billableService->category->value,
                'price' => (float) $billableService->price,
                'is_active' => $billableService->is_active,
            ],
        );

        return redirect()
            ->route('billable-services.edit', $billableService)
            ->with('success', 'Service updated successfully.');
    }
}
