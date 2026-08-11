<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CompanySettingsController extends Controller
{
    public function index(Request $request)
    {
        return view('settings.index', ['company' => $request->user()->company]);
    }

    /**
     * Danışman notu: barkod okutulurken girilen miktar bir eşiği aşarsa
     * mobil uygulama onay istesin, bu eşiği şirket admini belirlesin.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'scan_quantity_warning_threshold' => 'nullable|integer|min:1',
        ]);

        $request->user()->company->update([
            'scan_quantity_warning_threshold' => $data['scan_quantity_warning_threshold'] ?? null,
        ]);

        return back()->with('status', 'Ayarlar kaydedildi.');
    }
}