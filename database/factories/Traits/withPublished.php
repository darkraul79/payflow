<?php

namespace Database\Factories\Traits;

use Illuminate\Database\Eloquent\Factories\Factory;

trait withPublished
{
    public function unpublished(): Factory
    {
        return $this->state(function () {
            return [
                'pubblished' => false,
            ];
        });
    }
}
