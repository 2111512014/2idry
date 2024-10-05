<?php

namespace App\Console\Commands;

use App\Models\PageSettings;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateSuperAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:superadmin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Buat Pengguna Dengan Role Super Admin';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $superAdmin['name']                  = $this->ask("Nama untuk Super Admin");
        $superAdmin['email']                 = $this->ask("Email untuk Super Admin");
        $superAdmin['status']                = 'Active';
        $superAdmin['auth']                  = 'Super Admin';
        $superAdmin['password']              = $this->secret("Password untuk Super Admin");
        $superAdmin['password_confirmation'] = $this->secret("Konfirmasi Password untuk Super Admin");

        $cekUser = User::where('email', $superAdmin['email'])->where('auth','Super Admin')->first();
        if($cekUser) {
            $this->error("User Super Admin sudah dibuat!");
            return -1;
        }

        $validator = Validator::make($superAdmin,[
            'name'      => ['required','string','max:255'],
            'email'     => ['required','string','email','max:255','unique:'.User::class],
            'password'  => ['required','confirmed',Password::defaults()]
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return -1;
        }
        DB::transaction(function() use($superAdmin, $cekUser){
            $role = Role::firstOrNew(['name' => 'Super Admin']);
            $role->name = 'Super Admin';
            $role->save();
            $superAdmin['password']  = bcrypt($superAdmin['password']);
            $newAdmin = User::create($superAdmin);
            
            $newAdmin->assignRole('Super Admin');
            
            $cekUser = User::where('email', $superAdmin['email'])->where('auth', 'Super Admin')->first();

            PageSettings::create([
                'id_admin' => $cekUser->id,
                'judul'   => 'E-Laundry'
            ]);
        });

        $this->info("User " .$superAdmin['email']. " Berhasil dibuat :)");
    }
}
