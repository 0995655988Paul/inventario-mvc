<?php

namespace App\Controllers;

use App\Models\ProductoModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Productos extends BaseController
{
    // Lee y muestra los productos.
    public function index()
    {
        $modelo = new ProductoModel();
        $productos = $modelo->orderBy('id', 'DESC')->findAll();
        return view('productos/index', [
            'productos' => $productos,
            'usuario' => session()->get('usuario'),
        ]);
    }

    public function nuevo()
    {
        return $this->formulario();
    }

    // Valida y crea el producto.
    public function guardar()
    {
        $modelo = new ProductoModel();
        $datos = $this->datosFormulario();

        if ($modelo->insert($datos) === false) {
            $this->response->setStatusCode(422);

            return $this->formulario(null, $datos, $modelo->errors());
        }

        return redirect()->to(site_url('productos'))
            ->with('exito', 'Alimento creado correctamente.');
    }

    public function editar($id)
    {
        $modelo = new ProductoModel();
        $producto = $this->buscarProducto($modelo, $id);

        return $this->formulario($producto);
    }

    // Actualiza el producto.
    public function actualizar($id)
    {
        $modelo = new ProductoModel();
        $producto = $this->buscarProducto($modelo, $id);
        $datos = $this->datosFormulario();

        if ($modelo->update($id, $datos) === false) {
            $this->response->setStatusCode(422);

            return $this->formulario($producto, $datos, $modelo->errors());
        }

        return redirect()->to(site_url('productos'))
            ->with('exito', 'Alimento actualizado correctamente.');
    }

    // Elimina el producto mediante POST.
    public function eliminar($id)
    {
        $modelo = new ProductoModel();
        $this->buscarProducto($modelo, $id);

        if ($modelo->delete($id) === false) {
            return redirect()->to(site_url('productos'))
                ->with('error', 'No se pudo eliminar el alimento.');
        }

        return redirect()->to(site_url('productos'))
            ->with('exito', 'Alimento eliminado correctamente.');
    }

    // Recoge únicamente los campos permitidos.
    private function datosFormulario(): array
    {
        $datos = [];

        foreach (['nombre', 'precio', 'stock'] as $campo) {
            $valor = $this->request->getPost($campo);
            $datos[$campo] = is_string($valor) ? trim($valor) : '';
        }

        return $datos;
    }

    private function buscarProducto(ProductoModel $modelo, $id): array
    {
        $producto = $modelo->find($id);

        if ($producto === null) {
            throw PageNotFoundException::forPageNotFound('Alimento no encontrado.');
        }

        return $producto;
    }

    // Comparte la vista de creación y edición.
    private function formulario(?array $producto = null, ?array $datos = null, array $errores = []): string
    {
        return view('productos/formulario', [
            'producto' => $producto,
            'datos' => $datos ?? $producto ?? ['nombre' => '', 'precio' => '', 'stock' => '0'],
            'errores' => $errores,
            'usuario' => session()->get('usuario'),
        ]);
    }
}
