<?php

namespace App\Http\Controllers;

use App\Models\PublishingPackage;
use Illuminate\Http\Request;

class PublishingPackageController extends Controller
{
    public function index()
    {
        $packages = PublishingPackage::latest()->get();

        return view('publishing_packages.index', compact('packages'));
    }

    public function create()
    {
        return view('publishing_packages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable|max:1000',
            'price' => 'nullable|numeric|min:0',
        ]);

        $package = PublishingPackage::create($request->only([
            'name',
            'description',
            'includes_editing',
            'includes_layout',
            'includes_cover_design',
            'includes_author_certificate',
            'includes_google_scholar',
            'requires_hki_registration',
            'default_print_quantity',
            'price',
        ]) + [
            'includes_editing' => (bool) $request->boolean('includes_editing'),
            'includes_layout' => (bool) $request->boolean('includes_layout'),
            'includes_cover_design' => (bool) $request->boolean('includes_cover_design'),
            'includes_author_certificate' => (bool) $request->boolean('includes_author_certificate'),
            'includes_google_scholar' => (bool) $request->boolean('includes_google_scholar'),
            'requires_hki_registration' => (bool) $request->boolean('requires_hki_registration'),
        ]);

        $this->syncPackageItems($package, $request->input('package_items'));

        return redirect()->route('publishing-packages.index')->with('success', 'Paket penerbitan berhasil ditambahkan');
    }

    public function edit(PublishingPackage $publishingPackage)
    {
        return view('publishing_packages.edit', compact('publishingPackage'));
    }

    public function update(Request $request, PublishingPackage $publishingPackage)
    {
        $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable|max:1000',
            'price' => 'nullable|numeric|min:0',
        ]);

        $publishingPackage->update($request->only([
            'name',
            'description',
            'includes_editing',
            'includes_layout',
            'includes_cover_design',
            'includes_author_certificate',
            'includes_google_scholar',
            'requires_hki_registration',
            'default_print_quantity',
            'price',
        ]) + [
            'includes_editing' => (bool) $request->boolean('includes_editing'),
            'includes_layout' => (bool) $request->boolean('includes_layout'),
            'includes_cover_design' => (bool) $request->boolean('includes_cover_design'),
            'includes_author_certificate' => (bool) $request->boolean('includes_author_certificate'),
            'includes_google_scholar' => (bool) $request->boolean('includes_google_scholar'),
            'requires_hki_registration' => (bool) $request->boolean('requires_hki_registration'),
        ]);

        $this->syncPackageItems($publishingPackage, $request->input('package_items'));

        return redirect()->route('publishing-packages.index')->with('success', 'Paket penerbitan berhasil diperbarui');
    }

    public function destroy(PublishingPackage $publishingPackage)
    {
        $publishingPackage->delete();

        return redirect()->route('publishing-packages.index')->with('success', 'Paket penerbitan berhasil dihapus');
    }

    protected function syncPackageItems(PublishingPackage $package, ?string $items): void
    {
        $package->items()->delete();

        foreach (preg_split('/\r\n|\r|\n/', trim((string) $items)) ?: [] as $index => $line) {
            $name = trim($line);

            if ($name === '') {
                continue;
            }

            $package->items()->create([
                'name' => $name,
                'sort_order' => $index,
                'is_required' => true,
            ]);
        }
    }
}
