<?php

namespace Database\Factories\Traits;

use Illuminate\Database\Eloquent\Factories\Factory;

trait withDonaciones
{
    public function donacion(): Factory
    {
        return $this->state(function () {
            return [
                'donacion' => true,
            ];
        });
    }
}
