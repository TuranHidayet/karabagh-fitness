<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePackageRequest;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    // Bütün paketləri göstərmək
    public function index()
    {
        return Package::all();
    }

    // Yeni paket yaratmaq
    public function store(StorePackageRequest $request)
    {
        $data = $request->validated();

        $package = Package::create($data);

        return response()->json($package, 201);
    }

    // Tək paket göstərmək
    public function show(Package $package)
    {
        return $package;
    }

    // Paket yeniləmək
    public function update(StorePackageRequest $request, Package $package)
    {
        $data = $request->validated();

        $package->update($data);

        return response()->json($package);
    }

    // Paket silmək
    public function destroy(Package $package)
    {
        $package->delete();

        return response()->json(['message' => 'Paket silindi.']);
    }
}
