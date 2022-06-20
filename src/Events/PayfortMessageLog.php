<?php

namespace TamkeenTech\Payfort\Events;

use Illuminate\Foundation\Events\Dispatchable;

class PayfortMessageLog
{
    use Dispatchable;

    public $request;

    public $response;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}
