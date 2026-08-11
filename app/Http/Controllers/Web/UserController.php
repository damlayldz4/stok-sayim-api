<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * User modeli BelongsToCompany trait'ini kullanmıyor (kasıtlı —
     * sistem yöneticisinin company_id'si zaten null, trait'i buraya
     * eklemek gereksiz karmaşıklık yaratırdı). Bu yüzden şirket
     * filtresini burada elle uyguluyoruz.
     */
    public function index(Request $request)
    {
        $users = User::where('company_id', $request->user()->company_id)
            ->orderBy('name')
            ->paginate(20);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:company_admin,count_staff',
        ]);

        User::create([
            'company_id' => $request->user()->company_id,
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'is_active' => true,
        ]);

        return redirect()->route('users.index')->with('status', 'Kullanıcı oluşturuldu.');
    }

    public function edit(Request $request, User $user)
    {
        $this->ensureSameCompany($request, $user);

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->ensureSameCompany($request, $user);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,'.$user->id,
            'role' => 'required|in:company_admin,count_staff',
        ]);

        $user->update($data);

        return redirect()->route('users.index')->with('status', 'Kullanıcı güncellendi.');
    }

    /** Şifreyi admin belirliyor (doküman md.3.2: "Kullanıcılara kullanıcı adı ve şifre vermek"). */
    public function resetPassword(Request $request, User $user)
    {
        $this->ensureSameCompany($request, $user);

        $data = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update(['password' => Hash::make($data['password'])]);

        return back()->with('status', 'Şifre güncellendi.');
    }

    /** Kullanıcı silinmez, aktif/pasif arasında geçiş yapılır. Kendi hesabını pasif yapamaz. */
    public function toggle(Request $request, User $user)
    {
        $this->ensureSameCompany($request, $user);

        abort_if($user->id === $request->user()->id, 422, 'Kendi hesabını pasif yapamazsın.');

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('status', $user->is_active ? 'Kullanıcı aktif yapıldı.' : 'Kullanıcı pasif yapıldı.');
    }

    private function ensureSameCompany(Request $request, User $user): void
    {
        abort_if($user->company_id !== $request->user()->company_id, 404);
    }
}
