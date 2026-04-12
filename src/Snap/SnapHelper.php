<?php

namespace Artemis\Snap;

class SnapHelper
{
    public static function timestamp(): string
    {
        return date('Y-m-d\TH:i:sP');
    }

    public static function generateExternalId(): string
    {
        return uniqid('', true);
    }

    public static function generateReferenceNo(): string
    {
        return strtoupper(uniqid('REF-'));
    }
}