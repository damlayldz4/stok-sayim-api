<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Doküman md.3.1 (Sistem Yöneticisi):
 * - Yeni müşteri şirketi oluşturmak
 * - Şirket için ilk admin hesabını oluşturmak
 * - Şirket adminine kullanıcı adı ve şifre vermek
 * - Şirket hesabını aktif veya pasif yapmak
 * - Şirketleri listelemek
 *
 * Şirket oluşturma ile ilk admin hesabı oluşturma tek adımda yapılıyor
 * (aynı kural: sistem yöneticisi şirketi kurduktan sonra günlük işlere
 * karışmıyor, ilk admin'i o an atayıp bırakıyor).
 */
class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::withCount('users')->orderBy('name')->paginate(20);

        return view('companies.index', compact('companies'));
    }

    public function create()
    {
        return view('companies.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'admin_name' => 'required|string|max:255',
            'admin_username' => 'required|string|max:255|unique:users,username',
            'admin_email' => 'nullable|email|max:255|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        $company = Company::create([
            'name' => $data['name'],
            'is_active' => true,
        ]);

        User::create([
            'company_id' => $company->id,
            'name' => $data['admin_name'],
            'username' => $data['admin_username'],
            'email' => $data['admin_email'] ?? null,
            'password' => Hash::make($data['admin_password']),
            'role' => UserRole::CompanyAdmin,
            'is_active' => true,
        ]);

        return redirect()->route('companies.index')->with('status', 'Şirket ve admin hesabı oluşturuldu.');
    }

    /** Doküman kuralı: şirket silinmez, aktif/pasif arasında geçiş yapılır. */
    public function toggle(Company $company)
    {
        $company->update(['is_active' => ! $company->is_active]);

        return back()->with('status', $company->is_active ? 'Şirket aktif yapıldı.' : 'Şirket pasif yapıldı.');
    }
}
