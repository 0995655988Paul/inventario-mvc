<?php

declare(strict_types=1);

use CodeIgniter\Security\Exceptions\SecurityException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\TestResponse;
use Config\Database;
use PHPUnit\Framework\Attributes\DataProvider;

final class RegistroFeatureTest extends CIUnitTestCase
{
    use DatabaseTestTrait {
        setUpDatabase as private setUpIsolatedDatabase;
    }
    use FeatureTestTrait;

    protected $DBGroup = 'tests';
    protected $namespace = 'App';

    protected function setUp(): void
    {
        $this->resetServices();
        parent::setUp();
        helper('form');
    }

    protected function setUpDatabase(): void
    {
        // Evita usar la base de datos real.
        $config = config(Database::class);
        $this->assertSame('testing', ENVIRONMENT);
        $this->assertSame('tests', $config->defaultGroup);
        $this->assertSame('SQLite3', $config->tests['DBDriver']);
        $this->assertSame(':memory:', $config->tests['database']);

        $this->setUpIsolatedDatabase();
    }

    public function testRegistrationFormHasEmptyPasswordFieldsAndCsrfToken(): void
    {
        $result = $this->get('/registro');

        $result->assertOK();
        foreach (['usuario', 'correo', 'telefono'] as $field) {
            $result->assertSeeInField($field, '');
        }
        $result->assertSeeInField(csrf_token(), csrf_hash());
        $this->assertPasswordsAreEmpty($result);
    }

    public function testValidRegistrationStoresNormalizedDataAndBcryptWithoutLoggingIn(): void
    {
        $data = $this->validRegistration([
            'usuario' => '  Paula.Test_1  ',
            'correo' => '  Paula.Test@EXAMPLE.COM  ',
            'telefono' => ' +593 (99) 123-4567 ',
        ]);

        $result = $this->register($data);

        $result->assertRedirectTo('/');
        $result->assertSessionHas('exito');
        $result->assertSessionMissing('usuario_id');
        $result->assertSessionMissing('usuario');

        $rows = $this->db->table('usuarios')->get()->getResultArray();
        $this->assertCount(1, $rows);
        $this->assertSame('paula.test_1', $rows[0]['usuario']);
        $this->assertSame('paula.test@example.com', $rows[0]['correo']);
        $this->assertSame('+593991234567', $rows[0]['telefono']);
        $this->assertNotSame($data['password'], $rows[0]['password']);
        $this->assertSame('bcrypt', password_get_info($rows[0]['password'])['algoName']);
        $this->assertTrue(password_verify($data['password'], $rows[0]['password']));
    }

    #[DataProvider('validBoundaryProvider')]
    public function testRegistrationAcceptsTheDocumentedLengthBoundaries(array $changes): void
    {
        $data = $this->validRegistration($changes);
        $result = $this->register($data);

        $result->assertRedirectTo('/');
        $account = $this->db->table('usuarios')->get()->getRowArray();
        $this->assertNotNull($account);
        $this->assertTrue(password_verify($data['password'], $account['password']));
    }

    public static function validBoundaryProvider(): iterable
    {
        yield 'mínimos permitidos' => [[
            'usuario' => 'abc',
            'telefono' => '1234567',
            'password' => 'Aa1!bbbb',
            'confirmar_password' => 'Aa1!bbbb',
        ]];
        yield 'máximos permitidos' => [[
            'usuario' => str_repeat('a', 30),
            'telefono' => '+123456789012345',
            'password' => 'Aa1!' . str_repeat('b', 68),
            'confirmar_password' => 'Aa1!' . str_repeat('b', 68),
        ]];
    }

    #[DataProvider('invalidRegistrationProvider')]
    public function testInvalidRegistrationReturns422AndDoesNotCreateAnAccount(array $changes, string $errorField): void
    {
        $result = $this->register($this->validRegistration($changes));

        $result->assertStatus(422);
        $result->assertNotRedirect();
        $result->assertSee($errorField, 'p.error-campo');
        $this->assertSame(0, $this->db->table('usuarios')->countAllResults());
        $this->assertPasswordsAreEmpty($result);
        $result->assertSessionMissing('usuario_id');
    }

    public static function invalidRegistrationProvider(): iterable
    {
        yield 'usuario requerido' => [['usuario' => ''], 'usuario'];
        yield 'usuario corto' => [['usuario' => 'ab'], 'usuario'];
        yield 'usuario largo' => [['usuario' => str_repeat('a', 31)], 'usuario'];
        yield 'usuario con caracteres ajenos' => [['usuario' => 'paúl'], 'usuario'];
        yield 'usuario con espacios interiores' => [['usuario' => 'paul nuevo'], 'usuario'];
        yield 'correo requerido' => [['correo' => ''], 'correo'];
        yield 'correo inválido' => [['correo' => 'sin-arroba'], 'correo'];
        yield 'correo largo' => [['correo' => str_repeat('a', 245) . '@example.com'], 'correo'];
        yield 'teléfono requerido' => [['telefono' => ''], 'teléfono'];
        yield 'teléfono corto' => [['telefono' => '123456'], 'teléfono'];
        yield 'teléfono largo' => [['telefono' => '1234567890123456'], 'teléfono'];
        yield 'teléfono con letras' => [['telefono' => '+59399abc1234'], 'teléfono'];
        yield 'teléfono con más interior' => [['telefono' => '593+991234567'], 'teléfono'];
        yield 'contraseña requerida' => [['password' => '', 'confirmar_password' => ''], 'contraseña'];
        yield 'contraseña corta' => [['password' => 'Ab1!xyz', 'confirmar_password' => 'Ab1!xyz'], 'contraseña'];
        yield 'contraseña superior a bcrypt' => [['password' => 'Aa1!' . str_repeat('b', 69), 'confirmar_password' => 'Aa1!' . str_repeat('b', 69)], 'contraseña'];
        yield 'límite bcrypt contado en bytes' => [['password' => 'Aa1!' . str_repeat('é', 35), 'confirmar_password' => 'Aa1!' . str_repeat('é', 35)], 'contraseña'];
        yield 'contraseña sin mayúscula' => [['password' => 'segura123!', 'confirmar_password' => 'segura123!'], 'contraseña'];
        yield 'contraseña sin minúscula' => [['password' => 'SEGURA123!', 'confirmar_password' => 'SEGURA123!'], 'contraseña'];
        yield 'contraseña sin número' => [['password' => 'SeguraAbc!', 'confirmar_password' => 'SeguraAbc!'], 'contraseña'];
        yield 'contraseña sin símbolo' => [['password' => 'Segura1234', 'confirmar_password' => 'Segura1234'], 'contraseña'];
        yield 'contraseña con byte nulo' => [['password' => "Clave123!\0", 'confirmar_password' => "Clave123!\0"], 'contraseña'];
        yield 'confirmación requerida' => [['confirmar_password' => ''], 'confirmación'];
        yield 'confirmación diferente' => [['confirmar_password' => 'Distinta123!'], 'confirmación'];
    }

    public function testValidationRetainsContactFieldsAndNeverEchoesPasswords(): void
    {
        $data = $this->validRegistration(['confirmar_password' => 'OtraClave456!']);
        $result = $this->register($data);

        $result->assertStatus(422);
        foreach (['usuario', 'correo', 'telefono'] as $field) {
            $result->assertSeeInField($field, $data[$field]);
        }
        $this->assertStringNotContainsString($data['password'], $result->response()->getBody());
        $this->assertStringNotContainsString($data['confirmar_password'], $result->response()->getBody());
        $this->assertPasswordsAreEmpty($result);
    }

    #[DataProvider('duplicateRegistrationProvider')]
    public function testDuplicateUsernameOrEmailIsRejectedAfterNormalization(array $changes, string $errorField): void
    {
        $existing = $this->validRegistration();
        $hash = password_hash($existing['password'], PASSWORD_BCRYPT);
        $this->hasInDatabase('usuarios', [
            'usuario' => $existing['usuario'],
            'correo' => $existing['correo'],
            'telefono' => $existing['telefono'],
            'password' => $hash,
        ]);

        $result = $this->register($this->validRegistration($changes));

        $result->assertStatus(422);
        $result->assertSee($errorField, 'p.error-campo');
        $this->assertSame(1, $this->db->table('usuarios')->countAllResults());
        $this->seeInDatabase('usuarios', ['usuario' => $existing['usuario'], 'password' => $hash]);
        $result->assertSessionMissing('usuario_id');
    }

    public static function duplicateRegistrationProvider(): iterable
    {
        yield 'usuario normalizado' => [['usuario' => '  NUEVO_USUARIO  ', 'correo' => 'otro@example.com'], 'usuario'];
        yield 'correo normalizado' => [['usuario' => 'otro_usuario', 'correo' => '  NUEVO@EXAMPLE.COM  '], 'correo'];
    }

    public function testNewAccountCanLogInUsingTheStoredHash(): void
    {
        $data = $this->validRegistration();
        $this->register($data)->assertRedirectTo('/');
        $account = $this->db->table('usuarios')->where('usuario', $data['usuario'])->get()->getRowArray();

        $this->get('/')->assertOK();
        $result = $this->postWithCsrf('/login', ['usuario' => $data['usuario'], 'password' => $data['password']]);

        $result->assertRedirectTo('/productos');
        $result->assertSessionHas('usuario_id', $account['id']);
        $result->assertSessionHas('usuario', $data['usuario']);
        $this->assertTrue(service('session')->didRegenerate);
    }

    public function testRegistrationWithoutCsrfTokenIsRejected(): void
    {
        $this->get('/registro')->assertOK();
        $this->withSession($_SESSION);
        $this->expectException(SecurityException::class);

        try {
            $this->post('/registro', $this->validRegistration());
        } finally {
            $this->assertSame(0, $this->db->table('usuarios')->countAllResults());
        }
    }

    #[DataProvider('anonymousCrudProvider')]
    public function testAnonymousCrudRequestsRedirectToLoginWithoutChangingProducts(string $method, string $path, array $data): void
    {
        $this->hasInDatabase('productos', ['id' => 1, 'nombre' => 'Producto protegido', 'precio' => 12.50, 'stock' => 4]);
        $this->get('/')->assertOK();

        $result = $method === 'POST' ? $this->postWithCsrf($path, $data) : $this->get($path);

        $result->assertRedirectTo('/');
        $result->assertSessionMissing('usuario_id');
        $this->assertSame(1, $this->db->table('productos')->countAllResults());
        $this->seeInDatabase('productos', ['id' => 1, 'nombre' => 'Producto protegido', 'stock' => 4]);
    }

    public static function anonymousCrudProvider(): iterable
    {
        yield 'listado' => ['GET', '/productos', []];
        yield 'formulario nuevo' => ['GET', '/productos/nuevo', []];
        yield 'formulario editar' => ['GET', '/productos/1/editar', []];
        yield 'crear' => ['POST', '/productos/guardar', ['nombre' => 'No permitido', 'precio' => 1, 'stock' => 99]];
        yield 'actualizar' => ['POST', '/productos/1/actualizar', ['nombre' => 'No permitido', 'precio' => 1, 'stock' => 99]];
        yield 'eliminar' => ['POST', '/productos/1/eliminar', []];
    }

    private function validRegistration(array $changes = []): array
    {
        return array_replace([
            'usuario' => 'nuevo_usuario',
            'correo' => 'nuevo@example.com',
            'telefono' => '+593991234567',
            'password' => 'ClaveSegura123!',
            'confirmar_password' => 'ClaveSegura123!',
        ], $changes);
    }

    private function register(array $data): TestResponse
    {
        $this->get('/registro')->assertOK();

        return $this->postWithCsrf('/registro', $data);
    }

    private function postWithCsrf(string $path, array $data): TestResponse
    {
        $tokenName = csrf_token();
        $token = csrf_hash();

        return $this->withSession($_SESSION)->post($path, $data + [$tokenName => $token]);
    }

    private function assertPasswordsAreEmpty(TestResponse $result): void
    {
        $document = new DOMDocument();
        @$document->loadHTML($result->response()->getBody());
        $fields = (new DOMXPath($document))->query('//input[@type="password"]');

        $this->assertCount(2, $fields);
        $names = [];
        foreach ($fields as $field) {
            $this->assertSame('', $field->getAttribute('value'));
            $names[] = $field->getAttribute('name');
        }
        sort($names);
        $this->assertSame(['confirmar_password', 'password'], $names);
    }
}
