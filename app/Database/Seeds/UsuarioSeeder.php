<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\UsuarioModel;

class UsuarioSeeder extends Seeder
{
    public function run()
    {
        $modelo = new UsuarioModel();

        if ($modelo->where('usuario', 'admin')->first() === null) {
            $modelo->insert([
                'usuario' => 'admin',
                'password' => password_hash('Admin123!', PASSWORD_BCRYPT),
            ]);
        }
    }
}