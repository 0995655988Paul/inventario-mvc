<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductoModel extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nombre', 'precio', 'stock'];

    // Valida también en el servidor.
    protected $validationRules = [
        'nombre' => 'required|max_length[100]',
        'precio' => 'required|regex_match[/^\d{1,8}(\.\d{1,2})?$/]',
        'stock' => 'required|is_natural|max_length[9]',
    ];

    protected $validationMessages = [
        'nombre' => [
            'required' => 'Escribe el nombre del alimento.',
            'max_length' => 'El nombre debe tener como máximo 100 caracteres.',
        ],
        'precio' => [
            'required' => 'Escribe el precio del alimento.',
            'regex_match' => 'El precio debe estar entre 0 y 99999999.99, con máximo dos decimales.',
        ],
        'stock' => [
            'required' => 'Escribe la cantidad disponible.',
            'is_natural' => 'La cantidad debe ser un número entero mayor o igual a cero.',
            'max_length' => 'La cantidad debe tener como máximo nueve dígitos.',
        ],
    ];
}
