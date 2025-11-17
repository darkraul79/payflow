<?php

namespace App\Console\Commands;

use DB;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;

class VerifyQueueWorkerCommand extends Command
{
    protected $signature = 'queue:verify
                            {--connection=default: The queue connection to check}';

    protected $description = 'Verifica que el queue worker está funcionando correctamente';

    public function handle(): int
    {
        $connection = $this->option('connection');

        $this->info('🔍 Verificando estado del queue worker...');
        $this->newLine();

        // 1. Verificar configuración de colas
        $this->checkQueueConfiguration($connection);

        // 2. Verificar trabajos pendientes
        $this->checkPendingJobs($connection);

        // 3. Verificar trabajos fallidos
        $this->checkFailedJobs();

        // 4. Sugerencias
        $this->showRecommendations();

        return self::SUCCESS;
    }

    private function checkQueueConfiguration(string $connection): void
    {
        $this->components->info('1. Configuración de Colas');

        $driver = config("queue.connections.$connection.driver");
        $default = config('queue.default');

        $this->table(
            ['Configuración', 'Valor'],
            [
                ['Driver actual', $driver],
                ['Conexión por defecto', $default],
                ['Entorno', app()->environment()],
            ]
        );

        if ($driver === 'sync') {
            $this->components->warn('⚠️ Estás usando el driver "sync" - los jobs se ejecutan inmediatamente, no en cola.');
            $this->components->info('Para producción, usa "database", "redis" o "sqs".');
        } else {
            $this->components->success('✓ Driver configurado correctamente para colas.');
        }

        $this->newLine();
    }

    private function checkPendingJobs(string $connection): void
    {
        $this->components->info('2. Trabajos Pendientes');

        try {
            $size = Queue::connection($connection)->size();

            if ($size > 0) {
                $this->components->warn("⚠️  Hay $size trabajo(s) pendiente(s) en la cola.");
                $this->components->info('Esto podría indicar que el worker no está corriendo o está sobrecargado.');
            } else {
                $this->components->success('✓ No hay trabajos pendientes en la cola.');
            }
        } catch (Exception $e) {
            $this->components->error('✗ Error al verificar trabajos pendientes: '.$e->getMessage());
        }

        $this->newLine();
    }

    private function checkFailedJobs(): void
    {
        $this->components->info('3. Trabajos Fallidos');

        try {
            $failedJobs = DB::table('failed_jobs')->count();

            if ($failedJobs > 0) {
                $this->components->warn("⚠️  Hay $failedJobs trabajo(s) fallido(s).");
                $this->components->info('Revisa los logs o ejecuta: php artisan queue:failed');
            } else {
                $this->components->success('✓ No hay trabajos fallidos.');
            }
        } catch (Exception) {
            $this->components->warn('⚠️ No se pudo verificar trabajos fallidos. ¿Existe la tabla failed_jobs?');
        }

        $this->newLine();
    }

    private function showRecommendations(): void
    {
        $this->components->info('📋 Recomendaciones');

        $driver = config('queue.default');

        $recommendations = [
            '• Para verificar que el worker está corriendo: ps aux | grep "queue:work"',
            '• Para iniciar el worker: php artisan queue:work',
            '• Para ver trabajos fallidos: php artisan queue:failed',
            '• Para reintentar trabajos fallidos: php artisan queue:retry all',
            '• Para monitorear en tiempo real: php artisan queue:monitor',
        ];

        if ($driver !== 'sync') {
            $recommendations[] = '• En producción, usa Supervisor para mantener el worker siempre activo';
            $recommendations[] = '• Verifica logs en: storage/logs/laravel.log';
        }

        foreach ($recommendations as $recommendation) {
            $this->line($recommendation);
        }

        $this->newLine();
    }
}
