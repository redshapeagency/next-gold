<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\StoreSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:manage settings');
    }

    public function index()
    {
        $settings = StoreSettings::first();
        
        if (!$settings) {
            $settings = StoreSettings::create([
                'store_name' => config('app.name'),
                'currency' => 'EUR',
                'timezone' => 'Europe/Rome',
                'date_format' => 'd/m/Y',
                'time_format' => 'H:i',
            ]);
        }

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'store_name' => 'required|string|max:255',
            'store_address' => 'nullable|string|max:500',
            'store_phone' => 'nullable|string|max:50',
            'store_email' => 'nullable|email|max:255',
            'store_website' => 'nullable|url|max:255',
            'store_vat' => 'nullable|string|max:50',
            'store_tax_code' => 'nullable|string|max:50',
            'currency' => 'required|string|max:3',
            'currency_symbol' => 'nullable|string|max:5',
            'timezone' => 'required|string|max:100',
            'date_format' => 'required|string|max:20',
            'time_format' => 'required|string|max:20',
            'default_language' => 'required|string|max:5',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'invoice_template' => 'nullable|string|max:50',
            'backup_enabled' => 'boolean',
            'backup_frequency' => 'nullable|string|in:daily,weekly,monthly',
            'backup_retention_days' => 'nullable|integer|min:1|max:365',
            'email_notifications' => 'boolean',
            'gold_price_auto_update' => 'boolean',
            'gold_price_update_frequency' => 'nullable|integer|min:1|max:1440',
        ]);

        $settings = StoreSettings::firstOrFail();
        $data = $request->except(['logo']);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }

            $logoPath = $this->handleLogoUpload($request->file('logo'));
            $data['logo_path'] = $logoPath;
        }

        $settings->update($data);

        return redirect()->route('settings.index')
            ->with('success', 'Impostazioni aggiornate con successo.');
    }

    public function removeLogo()
    {
        $settings = StoreSettings::firstOrFail();
        
        if ($settings->logo_path) {
            Storage::disk('public')->delete($settings->logo_path);
            $settings->update(['logo_path' => null]);
        }

        return back()->with('success', 'Logo rimosso con successo.');
    }

    private function handleLogoUpload($file)
    {
        $manager = new ImageManager(new Driver());
        
        // Generate unique filename
        $filename = 'logo_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = 'logos/' . $filename;
        
        // Resize and optimize logo
        $image = $manager->read($file->getRealPath());
        $image->resize(400, 200, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        
        // Save to storage
        Storage::disk('public')->put($path, $image->encode());
        
        return $path;
    }

    public function testEmail()
    {
        // TODO: Implement email test functionality
        return back()->with('info', 'Test email non ancora implementato.');
    }
}
