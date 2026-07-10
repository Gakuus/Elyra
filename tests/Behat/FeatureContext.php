<?php

declare(strict_types=1);

namespace Elyra\Tests\Behat;

use Behat\Behat\Context\Context;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\MinkExtension\Context\RawMinkContext;

class FeatureContext extends RawMinkContext implements Context
{
    private static bool $serverStarted = false;

    public function __construct()
    {
        if (!self::$serverStarted) {
            self::cleanupRateLimit();
            self::startServer();
            self::$serverStarted = true;
        }
    }

    private static function cleanupRateLimit(): void
    {
        $dir = __DIR__ . '/../../storage/rate-limit';
        if (is_dir($dir)) {
            /** @var list<string> $files */
            $files = glob($dir . '/*');
            array_map('unlink', $files);
        }
    }

    private static function startServer(): void
    {
        // Cargar .env
        $envFile = __DIR__ . '/../../.env';
        if (file_exists($envFile)) {
            /** @var list<string> $lines */
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (str_starts_with(trim($line), '#')) continue;
                if (str_contains($line, '=')) {
                    [$key, $value] = explode('=', $line, 2);
                    $_ENV[trim($key)] = trim($value);
                }
            }
        }
        $_ENV['APP_ENV'] = 'testing';

        // Matar servidor previo si existe
        $pid = exec('lsof -ti tcp:9876 2>/dev/null');
        if ($pid) {
            exec("kill -9 $pid 2>/dev/null");
            usleep(300000);
        }

        $docRoot = (string) realpath(__DIR__ . '/../../public');
        $routerScript = $docRoot . '/router.php';

        $cmd = sprintf(
            'nohup php -S 0.0.0.0:9876 -t %s %s > /dev/null 2>&1 & echo $!',
            escapeshellarg($docRoot),
            escapeshellarg($routerScript)
        );

        $output = trim((string) shell_exec($cmd));
        $pid = (int) $output;

        if ($pid <= 0) {
            throw new \RuntimeException('No se pudo iniciar el servidor PHP embebido en :9876');
        }

        register_shutdown_function(function () use ($pid) {
            exec("kill -9 $pid 2>/dev/null");
        });

        // Esperar a que responda
        for ($i = 0; $i < 15; $i++) {
            $fp = @fsockopen('127.0.0.1', 9876, $errno, $errstr, 1);
            if ($fp) {
                fclose($fp);
                return;
            }
            usleep(500000);
        }

        throw new \RuntimeException('El servidor PHP embebido no respondió después de 7.5s');
    }

    // ─── Navegación ─────────────────────────────────────────────────

    /**
     * @Given /^estoy en la página de (login|inicio|dashboard)$/
     * @When /^voy a la página de (login|inicio|dashboard)$/
     */
    public function voyALaPagina(string $pagina): void
    {
        $rutas = [
            'login' => '/login',
            'inicio' => '/',
            'dashboard' => '/dashboard',
        ];
        $ruta = $rutas[$pagina] ?? "/{$pagina}";
        $this->visitPath($ruta);
    }

    /**
     * @When /^voy a "([^"]+)"$/
     */
    public function voyA(string $url): void
    {
        $this->visitPath($url);
    }

    // ─── Formularios ────────────────────────────────────────────────

    /**
     * @Given /^relleno "([^"]+)" con "([^"]+)"$/
     */
    public function rellenoCon(string $campo, string $valor): void
    {
        $this->getSession()->getPage()->fillField($campo, $valor);
    }

    /**
     * @Given /^selecciono "([^"]+)" como "([^"]+)"$/
     */
    public function seleccionoComo(string $opcion, string $campo): void
    {
        $this->getSession()->getPage()->selectFieldOption($campo, $opcion);
    }

    /**
     * @When /^presiono "([^"]+)"$/
     */
    public function presiono(string $boton): void
    {
        $page = $this->getSession()->getPage();

        $button = $page->findButton($boton);
        if ($button === null) {
            $button = $page->find('css', "button[type=submit]");
        }
        if ($button === null) {
            throw new ElementNotFoundException(
                $this->getSession()->getDriver(),
                'button',
                'named',
                $boton
            );
        }
        $button->press();
    }

    // ─── Archivos ───────────────────────────────────────────────────

    /**
     * @Given /^subo el archivo "([^"]+)" a "([^"]+)"$/
     */
    public function suboArchivo(string $archivo, string $campo): void
    {
        $fullPath = __DIR__ . '/fixtures/' . $archivo;
        if (!file_exists($fullPath)) {
            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            file_put_contents($fullPath, '%PDF-1.4 dummy content');
        }
        $this->getSession()->getPage()->attachFileToField($campo, $fullPath);
    }

    // ─── Aserciones ─────────────────────────────────────────────────

    /**
     * @Then /^debería ver "([^"]+)"$/
     */
    public function deberiaVer(string $texto): void
    {
        $this->assertSession()->pageTextContains($texto);
    }

    /**
     * @Then /^no debería ver "([^"]+)"$/
     */
    public function noDeberiaVer(string $texto): void
    {
        $this->assertSession()->pageTextNotContains($texto);
    }

    /**
     * @Then /^la URL debería contener "([^"]+)"$/
     */
    public function urlDeberiaContener(string $fragmento): void
    {
        $this->assertSession()->addressMatches('/' . preg_quote($fragmento, '/') . '/');
    }

    /**
     * @Then /^debería estar en "([^"]+)"$/
     */
    public function deberiaEstarEn(string $url): void
    {
        $this->assertSession()->addressEquals($url);
    }

    /**
     * @Then /^debería ver un campo "([^"]+)"$/
     */
    public function deberiaVerCampo(string $campo): void
    {
        $field = $this->getSession()->getPage()->findField($campo);
        \PHPUnit\Framework\Assert::assertNotNull($field, "Campo '{$campo}' no encontrado");
    }

    // ─── Autenticación ──────────────────────────────────────────────

    /**
     * @Given /^estoy autenticado como "([^"]+)"$/
     */
    public function estoyAutenticadoComo(string $username): void
    {
        $this->visitPath('/logout');
        $this->visitPath('/login');
        $this->rellenoCon('username', $username);
        $this->rellenoCon('password', 'admin');
        $this->presiono('Iniciar Sesión');
    }

    /**
     * @Given /^estoy autenticado como "([^"]+)" con contraseña "([^"]+)"$/
     */
    public function estoyAutenticadoComoConPassword(string $username, string $password): void
    {
        $this->visitPath('/logout');
        $this->visitPath('/login');
        $this->rellenoCon('username', $username);
        $this->rellenoCon('password', $password);
        $this->presiono('Iniciar Sesión');
    }

    /**
     * @Given /^inicié sesión con "([^"]+)" y contraseña "([^"]+)"$/
     */
    public function inicieSesion(string $username, string $password): void
    {
        $this->voyALaPagina('login');
        $this->rellenoCon('username', $username);
        $this->rellenoCon('password', $password);
        $this->presiono('Iniciar Sesión');
    }

    /**
     * @Given /^espero (\d+) segundos?$/
     */
    public function espero(int $segundos): void
    {
        sleep($segundos);
    }
}
