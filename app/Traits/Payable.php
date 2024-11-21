<?php

namespace App\Traits;

trait Payable
{
    abstract public function setPaid();

    abstract public function setFailed();
}
