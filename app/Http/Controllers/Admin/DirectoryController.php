<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Interest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DirectoryController extends Controller
{
    public function index(): View
    {
        return view('admin.directory.index', [
            'cities'            => City::withCount(['users', 'mingles'])->orderBy('name')->get(),
            'interests'         => Interest::withCount('users')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function storeCity(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $data = $this->validateCity($request);
        $city = City::create($this->cityPayload($request, $data));

        return $this->directoryResponse($request, 'City added.', $city);
    }

    public function updateCity(Request $request, City $city): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $data = $this->validateCity($request, $city);
        $city->update($this->cityPayload($request, $data, $city));

        return $this->directoryResponse($request, 'City updated.', $city->fresh());
    }

    public function destroyCity(City $city): RedirectResponse
    {
        if ($city->users()->exists() || $city->mingles()->exists()) return back()->withErrors(['city' => 'This city is in use. Set it inactive instead of deleting it.']);
        if ($city->image_path) Storage::disk('public')->delete($city->image_path);
        $city->delete();
        return back()->with('status', 'City removed.');
    }

    public function storeInterest(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100', 'unique:interests'], 'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'], 'is_active' => ['nullable', 'boolean']]);
        $interest = Interest::create($data + ['is_active' => $request->boolean('is_active'), 'sort_order' => $data['sort_order'] ?? 999]);
        if ($request->expectsJson()) return response()->json(['message' => 'Interest added.', 'interest' => $interest]);
        return back()->with('status', 'Interest added.');
    }

    public function updateInterest(Request $request, Interest $interest): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100', Rule::unique('interests')->ignore($interest)], 'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'], 'is_active' => ['nullable', 'boolean']]);
        $interest->update($data + ['is_active' => $request->boolean('is_active'), 'sort_order' => $data['sort_order'] ?? $interest->sort_order]);
        if ($request->expectsJson()) return response()->json(['message' => 'Interest updated.', 'interest' => $interest->fresh()]);
        return back()->with('status', 'Interest updated.');
    }

    public function destroyInterest(Interest $interest): RedirectResponse
    {
        if ($interest->users()->exists()) return back()->withErrors(['interest' => 'This interest is in use. Set it inactive instead of deleting it.']);
        $interest->delete();
        return back()->with('status', 'Interest removed.');
    }

    private function validateCity(Request $request, ?City $city = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('cities')->ignore($city)],
            'code' => ['required', 'alpha_dash', 'max:30', Rule::unique('cities')->ignore($city)],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function cityPayload(Request $request, array $data, ?City $city = null): array
    {
        unset($data['image'], $data['remove_image']);
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            if ($city?->image_path) Storage::disk('public')->delete($city->image_path);
            $data['image_path'] = $request->file('image')->store('cities', 'public');
        } elseif ($request->boolean('remove_image') && $city?->image_path) {
            Storage::disk('public')->delete($city->image_path);
            $data['image_path'] = null;
        }

        return $data;
    }

    private function directoryResponse(Request $request, string $message, City $city): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if ($request->expectsJson()) return response()->json(['message' => $message, 'city' => $city]);

        return back()->with('status', $message);
    }
}
