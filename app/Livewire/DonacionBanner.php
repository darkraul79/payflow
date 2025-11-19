<?php

namespace App\Livewire;

use App\Enums\AddressType;
use App\Enums\DonationFrequency;
use App\Enums\DonationType;
use App\Enums\PaymentMethod;
use App\Models\Donation;
use App\Services\PaymentProcess;
use App\Support\PaymentMethodRepository;
use Closure;
use Exception;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class DonacionBanner extends Component
{
    #[Validate]
    public string $amount = '0';

    public string $payment_method = '';

    public string $amount_select = '0';

    public bool $amount_select_10 = false;

    public bool $amount_select_50 = false;

    public bool $amount_select_100 = false;

    public mixed $frequency = null;

    public mixed $needsCertificate = true;

    public string $type;

    public string $MerchantParameters = '';

    public string $MerchantSignature = '';

    public string $SignatureVersion = '';

    public string $prefix = '';

    public int $step = 1;

    public array $certificate = [
        'name' => '',
        'last_name' => '',
        'last_name2' => '',
        'company' => '',
        'address' => '',
        'nif' => '',
        'cp' => '',
        'city' => '',
        'province' => '',
        'email' => '',
        'phone' => '',
    ];

    public bool $isValid = false;

    public array $payments_methods = [];

    public string $form_url = '';

    public function render(): View
    {

        return view('livewire.donacion-banner');
    }

    public function updatedAmount($value): void
    {
        $this->amount = $value;
        $this->amount_select = '0';

    }

    public function updatedType(string $value): void
    {
        $paymentMethods = new PaymentMethodRepository;
        if ($value === DonationType::UNICA->value) {
            $this->frequency = null;
            $this->payments_methods = $paymentMethods->getPaymentsMethods(false)
                ->map(fn ($method) => $method->toArray())
                ->toArray();

            return;
        }

        $this->payments_methods = $paymentMethods->getPaymentsMethods(true)
            ->map(fn ($method) => $method->toArray())
            ->toArray();
        $this->frequency = $this->frequency ?: DonationFrequency::MENSUAL->value;
    }

    public function mount($prefix): void
    {
        $this->amount = 0;
        $this->prefix = $prefix;
    }

    #[On('resetDonation')]
    public function resetModal(): void
    {
        if ($this->prefix === 'modal') {
            $this->reset([
                'amount',
                'amount_select',
                'amount_select_10',
                'amount_select_50',
                'amount_select_100',
                'frequency',
                'needsCertificate',
                'type',
                'MerchantParameters',
                'MerchantSignature',
                'SignatureVersion',
                'step',
                'certificate',
                'isValid',
            ]);
        }
    }

    public function updatedAmountSelect($value): void
    {
        $this->amount = $value;
    }

    public function updatedAmountSelect10($value): void
    {
        if ($value) {
            $this->amount = '10';
            $this->amount_select_50 = false;
            $this->amount_select_100 = false;
        }
    }

    public function updatedAmountSelect50($value): void
    {
        if ($value) {
            $this->amount = '50';
            $this->amount_select_10 = false;
            $this->amount_select_100 = false;
        }
    }

    public function updatedAmountSelect100($value): void
    {
        if ($value) {
            $this->amount = '100';
            $this->amount_select_10 = false;
            $this->amount_select_50 = false;
        }
    }

    public function updatedFrequency($value): void
    {
        // No hacer nada con amount cuando cambia frequency
        // Este hook previene que Livewire interfiera con otros campos
    }

    /**
     * @throws Exception
     */
    public function toStep(int $step): void
    {

        if ($step > $this->step) {
            $this->validate();
        }

        if ($step == 4 && ! $this->needsCertificate) {
            $this->submit();
        } else {

            if ($step == 3 && ! $this->needsCertificate) {
                $step = 4;
            }
            $this->step = $step;
        }

    }

    /**
     * @throws Exception
     */
    public function submit(): void
    {

        $this->validate();

        $paymentProcess = new PaymentProcess(Donation::class, [
            'amount' => convertPriceNumber($this->amount),
            'type' => $this->type,
            'frequency' => $this->frequency ?? null,
            'payment_method' => $this->payment_method,
        ]);

        if ($this->needsCertificate) {
            $paymentProcess->modelo->addresses()->create([
                'type' => AddressType::CERTIFICATE->value,
                'name' => $this->certificate['name'],
                'last_name' => $this->certificate['last_name'],
                'last_name2' => $this->certificate['last_name2'],
                'company' => $this->certificate['company'] ?? null,
                'nif' => $this->certificate['nif'] ?? null,
                'address' => $this->certificate['address'] ?? '',
                'cp' => $this->certificate['cp'],
                'city' => $this->certificate['city'] ?? '',
                'province' => $this->certificate['province'] ?? '',
                'email' => $this->certificate['email'],
                'phone' => $this->certificate['phone'] ?? '',
            ]);
        }

        $formData = $paymentProcess->getFormRedSysData();

        $this->MerchantParameters = $formData['Ds_MerchantParameters'];
        $this->MerchantSignature = $formData['Ds_Signature'];
        $this->SignatureVersion = $formData['Ds_SignatureVersion'];
        $this->form_url = $formData['form_url'] ?? '';

        $this->dispatch('submit-redsys-form');

    }

    public function updatingAmount($value): void
    {
        $this->amount_select_10 = false;
        $this->amount_select_50 = false;
        $this->amount_select_100 = false;

        switch ($value) {
            case '10,00':
            case '10':
            case '10,0':
                $this->amount_select = '0';
                $this->amount_select_10 = true;
                $this->amount_select_50 = false;
                $this->amount_select_100 = false;
                break;
            case '50,00':
            case '50':
            case '50,0':
                $this->amount_select = '0';
                $this->amount_select_10 = false;
                $this->amount_select_50 = true;
                $this->amount_select_100 = false;
                break;
            case '100,00':
            case '100':
            case '100,0':
                $this->amount_select = '0';

                $this->amount_select_10 = false;
                $this->amount_select_50 = false;
                $this->amount_select_100 = true;
                break;
        }
    }

    protected function rules(): array
    {
        return match ($this->step) {
            1 => [
                'amount' => [
                    'required',
                    function (string $attribute, mixed $value, Closure $fail) {
                        $amount = convertPriceNumber($value);
                        if ($amount < 1) {
                            $fail('El importe debe ser mayor o igual a 1,00 €');
                        }
                    },
                ],
                'frequency' => 'required_if:type,'.DonationType::RECURRENTE->value,
                'type' => 'required|in:'.DonationType::UNICA->value.','.DonationType::RECURRENTE->value,
            ],
            2 => [
                'needsCertificate' => '',
            ],
            3 => [
                'certificate.name' => 'required|string|max:255',
                'certificate.last_name' => 'required|string|max:255',
                'certificate.last_name2' => 'required|string|max:255',
                'certificate.nif' => 'required|string|max:255',
                'certificate.cp' => 'required|string|max:5',
                'certificate.email' => 'required|email|max:255',
            ],
            4 => [
                // compruebo que si es donación recurrente el método de pago es tarjeta

                'payment_method' => [
                    Rule::enum(PaymentMethod::class)
                        ->when(
                            $this->type === DonationType::RECURRENTE->value,
                            fn ($rule) => $rule->only(PaymentMethod::TARJETA),
                        ),
                    'required', 'string', 'max:255',
                ],
            ]
        };

    }

    protected function messages(): array
    {
        return [
            'required' => 'El campo es obligatorio.',
            'type.required' => 'Debes seleccionar el tipo de donación.',
            'frequency.required_if' => 'Debes seleccionar la frecuencia de pago.',
            'min' => 'Debe ser mayor que 0.',
            'integer' => 'El campo debe ser un número entero.',
            'numeric' => 'El campo debe ser un número.',
            'payment_method.enum' => 'El método de pago no es válido.',
            'payment_method.required' => 'Debes seleccionar un método de pago.',
        ];

    }
}
