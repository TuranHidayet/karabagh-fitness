<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePackageRequest;
use App\Models\Package;
use App\Helpers\CommonHelper;

class PackageController extends Controller
{
    // Bütün paketləri göstərmək
    public function index()
    {
        $packages = Package::all();
        return CommonHelper::jsonResponse('success', 'Bütün paketlər uğurla gətirildi', $packages);
    }

    // Yeni paket yaratmaq
    public function store(StorePackageRequest $request)
    {
        $data = $request->validated();
        $package = Package::create($data);

        return CommonHelper::jsonResponse('success', 'Paket uğurla yaradıldı', $package, 201);
    }

    // Tək paket göstərmək
    public function show(Package $package)
    {
        return CommonHelper::jsonResponse('success', 'Paket uğurla gətirildi', $package);
    }

    // Paket yeniləmək
    public function update(StorePackageRequest $request, Package $package)
    {
        $data = $request->validated();
        $package->update($data);

        return CommonHelper::jsonResponse('success', 'Paket uğurla yeniləndi', $package);
    }

    // Paket silmək
    public function destroy(Package $package)
    {
        $package->delete();
        return CommonHelper::jsonResponse('success', 'Paket uğurla silindi', null);
    }
}
