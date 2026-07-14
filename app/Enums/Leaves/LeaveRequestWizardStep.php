<?php

namespace App\Enums\Leaves;

enum LeaveRequestWizardStep: int
{
    case Type = 1;
    case Period = 2;
    case Information = 3;
    case Review = 4;
    case Confirmation = 5;

    public function label(): string
    {
        return match ($this) {
            self::Type => 'Type',
            self::Period => 'Periode',
            self::Information => 'Informations',
            self::Review => 'Verification',
            self::Confirmation => 'Confirmation',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Type => 'Choisissez le type de conge adapte a votre situation.',
            self::Period => 'Renseignez la periode pour calculer la duree, la reprise et le solde.',
            self::Information => 'Completez uniquement les informations utiles pour ce type de demande.',
            self::Review => 'Relisez la synthese avant la soumission definitive.',
            self::Confirmation => 'Conservez la reference et suivez la prochaine etape.',
        };
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $step): array => ['value' => $step->value, 'label' => $step->label()],
            self::cases(),
        );
    }

    public static function fromInt(int $step): self
    {
        return self::tryFrom(max(1, min(5, $step))) ?? self::Type;
    }
}
