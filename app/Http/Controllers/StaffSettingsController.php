<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\StaffSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StaffSettingsController extends Controller
{
    /**
     * Show the settings form
     */
    public function edit()
    {
        $staffId = Auth::guard('staff')->id();
        $settings = StaffSetting::firstOrCreate(
            ['staff_id' => $staffId],
            ['staff_id' => $staffId]
        );
        
        // Fetch all deals for this staff
        $deals = Deal::where('staff_id', $staffId)->orderBy('order')->get();

        return view('staff.settings.edit', compact('settings', 'deals'));
    }

    /**
     * Update settings (Johor highlights only)
     */
    public function update(Request $request)
    {
        $staffId = Auth::guard('staff')->id();
        $settings = StaffSetting::firstOrCreate(['staff_id' => $staffId]);

        $validated = $request->validate([
            'johor_highlights_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        // Handle johor highlights image upload
        if ($request->hasFile('johor_highlights_image')) {
            // Delete old image if exists
            if ($settings->johor_highlights_image_path && Storage::disk('public')->exists($settings->johor_highlights_image_path)) {
                Storage::disk('public')->delete($settings->johor_highlights_image_path);
            }

            $file = $request->file('johor_highlights_image');
            $filename = 'johor_' . $staffId . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = Storage::disk('public')->putFileAs('staff_settings/highlights', $file, $filename);
            $settings->johor_highlights_image_path = $path;
        }

        $settings->save();

        return redirect()->route('staff.settings.edit')
            ->with('success', 'Settings updated successfully!');
    }

    /**
     * Store a new deal
     */
    public function storeDeal(Request $request)
    {
        $staffId = Auth::guard('staff')->id();

        $validated = $request->validate([
            'deal_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'deal_title' => 'nullable|string|max:100',
            'deal_description' => 'nullable|string|max:500',
        ]);

        // Handle deal image upload
        if ($request->hasFile('deal_image')) {
            $file = $request->file('deal_image');
            $filename = 'deal_' . $staffId . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = Storage::disk('public')->putFileAs('staff_settings/deals', $file, $filename);

            // Get max order
            $maxOrder = Deal::where('staff_id', $staffId)->max('order') ?? 0;

            Deal::create([
                'staff_id' => $staffId,
                'image_path' => $path,
                'title' => $request->deal_title,
                'description' => $request->deal_description,
                'order' => $maxOrder + 1,
                'active' => true,
            ]);
        }

        return redirect()->route('staff.settings.edit')
            ->with('success', 'Deal added successfully!');
    }

    /**
     * Show edit form for a deal
     */
    public function editDeal($dealId)
    {
        $deal = Deal::findOrFail($dealId);
        
        // Ensure staff can only edit their own deals
        if ($deal->staff_id !== Auth::guard('staff')->id()) {
            return redirect()->route('staff.settings.edit')
                ->with('error', 'Unauthorized action');
        }

        return view('staff.settings.edit-deal', compact('deal'));
    }

    /**
     * Update a deal
     */
    public function updateDeal(Request $request, $dealId)
    {
        $deal = Deal::findOrFail($dealId);
        
        // Ensure staff can only edit their own deals
        if ($deal->staff_id !== Auth::guard('staff')->id()) {
            return redirect()->route('staff.settings.edit')
                ->with('error', 'Unauthorized action');
        }

        $validated = $request->validate([
            'deal_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'deal_title' => 'nullable|string|max:100',
            'deal_description' => 'nullable|string|max:500',
        ]);

        // Handle deal image upload
        if ($request->hasFile('deal_image')) {
            // Delete old image
            if ($deal->image_path && Storage::disk('public')->exists($deal->image_path)) {
                Storage::disk('public')->delete($deal->image_path);
            }

            $file = $request->file('deal_image');
            $filename = 'deal_' . $deal->staff_id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = Storage::disk('public')->putFileAs('staff_settings/deals', $file, $filename);
            $deal->image_path = $path;
        }

        $deal->title = $request->deal_title;
        $deal->description = $request->deal_description;
        $deal->save();

        return redirect()->route('staff.settings.edit')
            ->with('success', 'Deal updated successfully!');
    }

    /**
     * Delete a deal
     */
    public function deleteDeal($dealId)
    {
        $deal = Deal::findOrFail($dealId);
        
        // Ensure staff can only delete their own deals
        if ($deal->staff_id !== Auth::guard('staff')->id()) {
            return redirect()->route('staff.settings.edit')
                ->with('error', 'Unauthorized action');
        }

        // Delete image
        if ($deal->image_path && Storage::disk('public')->exists($deal->image_path)) {
            Storage::disk('public')->delete($deal->image_path);
        }

        $deal->delete();

        return redirect()->route('staff.settings.edit')
            ->with('success', 'Deal deleted successfully!');
    }

    /**
     * Reorder deals
     */
    public function reorderDeals(Request $request)
    {
        $staffId = Auth::guard('staff')->id();
        $dealIds = $request->input('deal_ids', []);

        foreach ($dealIds as $index => $dealId) {
            $deal = Deal::where('staff_id', $staffId)->where('id', $dealId)->first();
            if ($deal) {
                $deal->update(['order' => $index]);
            }
        }

        return response()->json(['success' => true]);
    }
}
