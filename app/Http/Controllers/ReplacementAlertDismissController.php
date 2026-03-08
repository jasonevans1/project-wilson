<?php

namespace App\Http\Controllers;

use App\Models\AssetReplacementAlert;
use Illuminate\Http\RedirectResponse;

class ReplacementAlertDismissController extends Controller
{
    public function __invoke(AssetReplacementAlert $alert): RedirectResponse
    {
        if ($alert->dismissed_at === null) {
            $alert->update(['dismissed_at' => now()]);
        }

        return redirect()->route('replacement-tracking')
            ->with('status', __('Alert dismissed successfully.'));
    }
}
