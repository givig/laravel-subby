<?php

namespace Bpuig\Subby\Services\PaymentMethods;

use Bpuig\Subby\Contracts\PaymentMethodService;

class Free implements PaymentMethodService
{
    /**
     * Charge desired amount
     * @return void
     */
    public function charge()
    {
        // Nothing is charged, no exception is raised
    }
}
