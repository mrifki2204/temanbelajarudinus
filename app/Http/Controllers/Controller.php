<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Escape karakter khusus LIKE (% dan _) agar input user tidak bertindak
     * sebagai wildcard pada klausa WHERE ... LIKE.
     */
    protected function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $value);
    }
}
