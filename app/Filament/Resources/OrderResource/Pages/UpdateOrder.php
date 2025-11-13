<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Events\UpdateOrderStateEvent;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Concerns\HasRelationManagers;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class UpdateOrder extends Page implements HasForms
{
    use HasRelationManagers, InteractsWithForms, InteractsWithRecord;

    protected static string $resource = OrderResource::class;

    protected static ?string $title = 'Actualizar Estado Pedido';

    protected static ?string $breadcrumb = 'Actualizar Estado Pedido';

    protected static ?string $navigationLabel = 'Actualizar Estado Pedido';

    protected static string $view = 'filament.resources.order-resource.pages.update-order-state';

    public Order $pedido;

    public string $estado;

    public string $mensaje;

    protected ?string $heading = 'Actualizar Estado Pedido';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->pedido = Order::find($record);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('estado')
                    ->label('Estado')
                    ->options(fn () => method_exists($this->record, 'available_states') ? $this->record->available_states() : []),
                /*TextInput::make('mensaje')
                    ->helperText('Mensaje opcional, que el usuario podrá ver en su historial de pedidos')
                    ->label('Mensaje')
                    ->default(''),*/
            ]);

    }

    public function submit(): void
    {
        $campos = $this->validate([
            'estado' => 'required',
            'mensaje' => 'nullable|string|max:255',
        ]);

        $this->record->states()->create([
            'name' => $this->pedido->getStates()[$campos['estado']],
            'message' => $campos['mensaje'],
        ]);
        $this->estado = false;

        $this->record->touch('updated_at');

        $this->record->refresh();
        $this->pedido->refresh();

        UpdateOrderStateEvent::dispatch($this->record);

    }

    protected function messages(): array
    {
        return [
            'estado.required' => 'Debes seleccionar un estado.',
        ];

    }
}
