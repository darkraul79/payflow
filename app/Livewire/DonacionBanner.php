<?php

namespace App\Livewire;

use App\Http\Classes\PaymentProcess;
use App\Models\Donation;
use Exception;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

class DonacionBanner extends Component
{
    #[Validate]
    public string $amount = '0';

    public string $amount_select = '0';

    public string $frequency;

    public string $type = Donation::UNICA;

    public string $MerchantParameters = '';

    public string $MerchantSignature = '';

    public string $SignatureVersion = '';

    public bool $isValid = false;

    public function render(): View
    {
        //        $donacion = Donation::find(2);
        //        $redsys = new RedsysAPI;
        //        $formData = $redsys->getFormPagoRecurrente($donacion, false);
        //        $this->MerchantParameters = $formData['Ds_MerchantParameters'];
        //        $this->MerchantSignature = $formData['Ds_Signature'];
        //        $this->SignatureVersion = $formData['Ds_SignatureVersion'];
        //        dd($redsys);

        //        dd($donacion->payments()->latest()->first()->info);
        return view('livewire.donacion-banner');
    }

    public function updatedAmount($value): void
    {
        $this->amount_select = '0';

    }

    public function updatedType(): void
    {
        $this->frequency = $this->type === Donation::UNICA
            ? false
            : $this->frequency ?? Donation::FREQUENCY['MENSUAL'];
    }

    public function updatedAmountSelect($value): void
    {
        $this->amount = $value;
    }

    /**
     * @throws Exception
     */
    public function submit(): void
    {
        $this->validate();

        //        $this->isValid = true;

        $paymentProcess = new PaymentProcess(Donation::class, [
            'amount' => convertPriceNumber($this->amount),
            'type' => $this->type,
            'frequency' => $this->frequency ?? null,
        ]);
        $formData = $paymentProcess->getFormRedSysData();

        $this->MerchantParameters = $formData['Ds_MerchantParameters'];
        $this->MerchantSignature = $formData['Ds_Signature'];
        $this->SignatureVersion = $formData['Ds_SignatureVersion'];

        //        $this->dispatch('validForm');

        $this->isValid = true;
        $this->dispatch('submit-redsys-form');

    }

    protected function rules(): array
    {
        return [
            'amount' => 'required',
            'type' => 'required|in:' . Donation::UNICA . ',' . Donation::RECURRENTE,
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => 'El campo es obligatorio.',
            'min' => 'Debe ser mayor que 0.',
            'integer' => 'El campo debe ser un número entero.',
            'numeric' => 'El campo debe ser un número.',
        ];

    }
}
