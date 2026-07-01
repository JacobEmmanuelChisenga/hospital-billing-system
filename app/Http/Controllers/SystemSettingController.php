<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSystemSettingsRequest;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    public function __construct(
        private SettingsService $settingsService,
    ) {}

    /**
     * Edit hospital branding and billing thresholds.
     */
    public function edit(): View
    {
        return view('system-settings.edit', [
            'settings' => $this->settingsService->all(),
        ]);
    }

    /**
     * Save settings to the database and apply them immediately.
     */
    public function update(UpdateSystemSettingsRequest $request): RedirectResponse
    {
        $this->settingsService->update($request->validated(), $request->user());

        return redirect()
            ->route('system-settings.edit')
            ->with('success', 'System settings updated successfully.');
    }
}
