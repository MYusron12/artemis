<?php

namespace Artemis;

interface Middleware
{
    public function handle(Request $request, callable $next): void;
}