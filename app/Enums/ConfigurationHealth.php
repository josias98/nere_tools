<?php

namespace App\Enums;

enum ConfigurationHealth: string
{
    case Configured = 'configured';
    case Attention = 'attention';
    case Incomplete = 'incomplete';
    case Critical = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::Configured => 'Configuré',
            self::Attention => 'Attention requise',
            self::Incomplete => 'Incomplet',
            self::Critical => 'Erreur critique',
        };
    }
}
