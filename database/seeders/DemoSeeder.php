<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Product;
use App\Models\Shelf;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

/**
 * Test için hazır şirket, kullanıcılar, şube/depo/raf ve bir ürün oluşturur.
 * Çalıştırma: php artisan db:seed --class=DemoSeeder
 * (veya DatabaseSeeder::run() içine $this->call(DemoSeeder::class); ekle)
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'company_id' => null,
            'name' => 'Niyel Admin',
            'username' => 'niyel_admin',
            'email' => 'niyel_admin@test.com',
            'password' => bcrypt('password'),
            'role' => UserRole::SystemAdmin,
            'is_active' => true,
        ]);

        $company = Company::create([
            'name' => 'Test A.Ş.',
            'is_active' => true,
        ]);

        User::create([
            'company_id' => $company->id,
            'name' => 'Test Admin',
            'username' => 'admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => UserRole::CompanyAdmin,
            'is_active' => true,
        ]);

        User::create([
            'company_id' => $company->id,
            'name' => 'Test Sayım Personeli',
            'username' => 'sayici',
            'email' => 'sayici@test.com',
            'password' => bcrypt('password'),
            'role' => UserRole::CountStaff,
            'is_active' => true,
        ]);

        // İkinci bir şirket + admin: şirket izolasyonunu test etmek için.
        $company2 = Company::create([
            'name' => 'Diğer Şirket A.Ş.',
            'is_active' => true,
        ]);

        User::create([
            'company_id' => $company2->id,
            'name' => 'Diğer Şirket Admini',
            'username' => 'admin2',
            'email' => 'admin2@test.com',
            'password' => bcrypt('password'),
            'role' => UserRole::CompanyAdmin,
            'is_active' => true,
        ]);

        $branch = Branch::create([
            'company_id' => $company->id,
            'branch_code' => 'SB-001',
            'name' => 'Merkez Şube',
            'address' => 'Test Adres',
            'is_active' => true,
        ]);

        $warehouse = Warehouse::create([
            'branch_id' => $branch->id,
            'warehouse_code' => 'DP-001',
            'name' => 'Ana Depo',
            'is_active' => true,
        ]);

        $shelf = Shelf::create([
            'warehouse_id' => $warehouse->id,
            'shelf_code' => 'A-01',
            'is_active' => true,
        ]);

        Product::create([
            'company_id' => $company->id,
            'product_name' => 'Test Ürün 1',
            'product_code' => 'URN-001',
            'barcode' => '0001234567890',
            'expected_quantity' => 20,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'shelf_id' => $shelf->id,
            'is_active' => true,
        ]);

        $this->command->info('Demo veri hazır:');
        $this->command->info('  niyel_admin / password -> system_admin');
        $this->command->info('  admin / password   -> company_admin (Test A.Ş.)');
        $this->command->info('  sayici / password  -> count_staff (Test A.Ş.)');
        $this->command->info('  admin2 / password  -> company_admin (Diğer Şirket A.Ş., izolasyon testi için)');
        $this->command->info('  Barkod: 0001234567890 (beklenen miktar: 20)');
    }
}