<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Home extends BaseController
{
    public function index()
    {
        $this->response->setHeader('Cache-Control', 'no-store');

        if (session()->has('usuario_id')) {
            return redirect()->to(site_url('productos'));
        }

        return view('login');
    }

    public function registro()
    {
        $this->response->setHeader('Cache-Control', 'no-store');

        if (session()->has('usuario_id')) {
            return redirect()->to(site_url('productos'));
        }

        return view('registro', ['datos' => [], 'errores' => []]);
    }

    public function registrar()
    {
        $this->response->setHeader('Cache-Control', 'no-store');

        if (session()->has('usuario_id')) {
            return redirect()->to(site_url('productos'));
        }

        // Acepta texto y normaliza los datos.
        $datos = [
            'usuario' => strtolower(trim($this->campoRegistro('usuario'))),
            'correo' => strtolower(trim($this->campoRegistro('correo'))),
            'telefono' => preg_replace('/[\s().-]+/', '', $this->campoRegistro('telefono')),
        ];
        $password = $this->campoRegistro('password');
        $confirmacion = $this->campoRegistro('confirmar_password');

        $this->validateData($datos + [
            'password' => $password,
            'confirmar_password' => $confirmacion,
        ], [
            'usuario' => [
                'rules' => ['required', 'min_length[3]', 'max_length[30]', 'regex_match[/\A[a-z0-9._-]+\z/]', 'is_unique[usuarios.usuario]'],
                'errors' => [
                    'required' => 'El usuario es obligatorio.',
                    'min_length' => 'El usuario debe tener al menos 3 caracteres.',
                    'max_length' => 'El usuario no puede superar los 30 caracteres.',
                    'regex_match' => 'El usuario solo admite letras, números, punto, guion y guion bajo.',
                    'is_unique' => 'Ese usuario ya está registrado.',
                ],
            ],
            'correo' => [
                'rules' => ['required', 'max_length[254]', 'valid_email', 'is_unique[usuarios.correo]'],
                'errors' => [
                    'required' => 'El correo es obligatorio.',
                    'max_length' => 'El correo no puede superar los 254 caracteres.',
                    'valid_email' => 'Escribe un correo válido, por ejemplo nombre@ejemplo.com.',
                    'is_unique' => 'Ese correo ya está registrado.',
                ],
            ],
            'telefono' => [
                'rules' => ['required', 'regex_match[/\A\+?[0-9]{7,15}\z/]'],
                'errors' => [
                    'required' => 'El teléfono es obligatorio.',
                    'regex_match' => 'El teléfono debe tener entre 7 y 15 dígitos; puede empezar con +.',
                ],
            ],
            'password' => [
                'rules' => ['required', 'min_length[8]', 'max_length[72]'],
                'errors' => [
                    'required' => 'La contraseña es obligatoria.',
                    'min_length' => 'La contraseña debe tener al menos 8 caracteres.',
                    'max_length' => 'La contraseña no puede superar los 72 bytes.',
                ],
            ],
            'confirmar_password' => [
                'rules' => ['required', 'matches[password]'],
                'errors' => [
                    'required' => 'La confirmación de contraseña es obligatoria.',
                    'matches' => 'La confirmación no coincide con la contraseña.',
                ],
            ],
        ]);

        $errores = $this->validator->getErrors();

        // bcrypt admite hasta 72 bytes.
        if (strlen($password) > 72 || str_contains($password, "\0")) {
            $errores['password'] = 'La contraseña no puede superar los 72 bytes ni incluir caracteres nulos.';
        } elseif (! isset($errores['password'])
            && ! preg_match('/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^a-zA-Z0-9\s])/u', $password)) {
            $errores['password'] = 'La contraseña debe incluir mayúscula, minúscula, número y símbolo.';
        }

        if ($errores !== []) {
            $this->response->setStatusCode(422);

            return view('registro', ['datos' => $datos, 'errores' => $errores]);
        }

        try {
            $modelo = new UsuarioModel();
            $creado = $modelo->insert($datos + [
                'password' => password_hash($password, PASSWORD_BCRYPT),
            ]);
        } catch (DatabaseException $exception) {
            $creado = false;
        }

        if ($creado === false) {
            $this->response->setStatusCode(500);

            return view('registro', [
                'datos' => $datos,
                'errores' => ['registro' => 'No se pudo crear la cuenta. Inténtalo de nuevo.'],
            ]);
        }

        return redirect()->to(site_url('/'))
            ->with('exito', 'Cuenta creada. Ya puedes iniciar sesión.');
    }

    private function campoRegistro(string $campo): string
    {
        $valor = $this->request->getPost($campo);

        return is_string($valor) ? $valor : '';
    }

    public function autenticar()
    {
        $usuario = $this->request->getPost('usuario');
        $password = $this->request->getPost('password');
        $registro = null;

        if (is_string($usuario) && is_string($password)
            && strlen($usuario) <= 50 && strlen($password) <= 72) {
            $modelo = new UsuarioModel();
            $registro = $modelo->where('usuario', strtolower(trim($usuario)))->first();
        }

        // Comprueba la contraseña contra su hash.
        if ($registro === null || ! password_verify($password, $registro['password'])) {
            return redirect()->to(site_url('/'))
                ->with('error', 'Usuario o contraseña incorrectos.');
        }

        // Guarda al usuario en la sesión.
        session()->regenerate(true);
        session()->set([
            'usuario_id' => $registro['id'],
            'usuario' => $registro['usuario'],
        ]);

        return redirect()->to(site_url('productos'));
    }

    public function salir()
    {
        session()->destroy();

        return redirect()->to(site_url('/'));
    }

    // Abrir /salir por GET conserva la sesión.
    public function salirPorGet()
    {
        if (session()->has('usuario_id')) {
            return redirect()->to(site_url('productos'))
                ->with('info', 'Para cerrar sesión, usa el botón Cerrar sesión del inventario.');
        }

        return redirect()->to(site_url('/'))
            ->with('info', 'No hay una sesión activa para cerrar.');
    }
}
