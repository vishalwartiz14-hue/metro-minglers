<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserReportController extends Controller
{
    public function index(): View
    {
        return view('admin.reports.index', [
            'reports' => UserReport::query()->with([
                'reporter:id,name,email',
                'reported:id,name,email',
            ])->latest()->paginate(25),
        ]);
    }

    public function update(Request $request, UserReport $report): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:open,reviewed,closed']]);
        $report->update($data);

        return back()->with('status', 'Report status updated.');
    }
}
