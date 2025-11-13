<?php

namespace App\Models\Traits;

trait WithCommonAttributes
{
    protected function getIsHomePageAttribute(): bool
    {

        if (isset($this->is_home)) {
            return $this->is_home;
        }

        return false;
    }
}
