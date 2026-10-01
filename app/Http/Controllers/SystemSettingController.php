<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SystemSettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::all()->keyBy('key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'logo_path'       => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'nama_sistem'     => 'required|string|max:100',
            'universitas'     => 'required|string|max:150',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo_path')) {
            $old = SystemSetting::get('logo_path');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
            $path = $request->file('logo_path')->store('logos', 'public');
            SystemSetting::set('logo_path', $path);
        }

        // Handle logo deletion checkbox
        if ($request->boolean("delete_logo_path")) {
            $old = SystemSetting::get('logo_path');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
            SystemSetting::set('logo_path', null);
        }

        // Save all text fields
        $textFields = [
            'nama_sistem',
            'universitas',
        ];
        foreach ($textFields as $field) {
            SystemSetting::set($field, $request->input($field));
        }

        SystemSetting::clearAllCache();

        return back()->with('success', 'Konfigurasi profil universitas berhasil disimpan.');
    }
}
