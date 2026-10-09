<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AgregarContactoUsuarios extends Migration
{
    public function up()
    {
        $this->forge->addColumn('usuarios', [
            'correo' => [
                'type' => 'VARCHAR',
                'constraint' => 254,
                'null' => true,
            ],
            'telefono' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
                'null' => true,
            ],
        ]);

        $this->forge->addUniqueKey('correo');
        $this->forge->processIndexes('usuarios');
    }

    public function down()
    {
        $this->forge->dropKey('usuarios', 'usuarios_correo');
        $this->forge->dropColumn('usuarios', ['correo', 'telefono']);
    }
}
