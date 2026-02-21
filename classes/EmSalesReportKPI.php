<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class EmSalesReportKPI
{
    // ===========================================================
    // MÉTODO 1: calculateVariation
    // ===========================================================

    /**
     * Calcula la variación porcentual entre el valor actual y el anterior.
     *
     * Casos:
     *  - previous = 0, current > 0  → +100% (nuevo, sin referencia)
     *  - previous = 0, current = 0  → 0% neutral
     *  - previous > 0, current = 0  → -100%
     *  - caso general               → ((current - previous) / previous) * 100
     *
     * @param float $current   Valor del período actual
     * @param float $previous  Valor del período anterior
     * @return array{value: float, direction: string, formatted: string}
     */
    public static function calculateVariation(float $current, float $previous): array
    {
        if ($previous == 0.0 && $current == 0.0) {
            return [
                'value'     => 0.0,
                'direction' => 'neutral',
                'formatted' => '0%',
            ];
        }

        if ($previous == 0.0) {
            // No existe referencia anterior pero sí hay valor actual → crecimiento absoluto
            return [
                'value'     => 100.0,
                'direction' => 'up',
                'formatted' => '+100%',
            ];
        }

        $value = round((($current - $previous) / abs($previous)) * 100, 1);

        if ($value > 0) {
            $direction = 'up';
            $formatted = '+' . $value . '%';
        } elseif ($value < 0) {
            $direction = 'down';
            $formatted = $value . '%';   // ya lleva el signo negativo
        } else {
            $direction = 'neutral';
            $formatted = '0%';
        }

        return [
            'value'     => $value,
            'direction' => $direction,
            'formatted' => $formatted,
        ];
    }

    // ===========================================================
    // MÉTODO 2: getPreviousPeriod
    // ===========================================================

    /**
     * Calcula el período anterior de la misma duración para la comparativa.
     *
     * Ejemplo:
     *   dateFrom = 2024-02-01, dateTo = 2024-02-29 (29 días)
     *   previousTo   = 2024-01-31 (dateFrom - 1 día)
     *   previousFrom = 2024-01-02 (previousTo - 28 días, duración exacta)
     *
     * @param string $dateFrom  Fecha inicio en formato Y-m-d
     * @param string $dateTo    Fecha fin en formato Y-m-d
     * @return array{from: string, to: string}
     */
    public static function getPreviousPeriod(string $dateFrom, string $dateTo): array
    {
        $from = new DateTime($dateFrom);
        $to   = new DateTime($dateTo);

        // Número de días del período actual (inclusive)
        $durationDays = (int) $from->diff($to)->days; // diff entre fechas es siempre ≥ 0

        // El período anterior termina el día justo antes del inicio actual
        $previousTo = clone $from;
        $previousTo->modify('-1 day');

        // El período anterior empieza exactamente $durationDays antes del previousTo
        $previousFrom = clone $previousTo;
        $previousFrom->modify('-' . $durationDays . ' days');

        return [
            'from' => $previousFrom->format('Y-m-d'),
            'to'   => $previousTo->format('Y-m-d'),
        ];
    }

    // ===========================================================
    // MÉTODO 3: formatCurrency
    // ===========================================================

    /**
     * Formatea un importe con el símbolo y formato de la moneda de PrestaShop.
     *
     * Usa Tools::displayPrice() que respeta el formato configurado en la tienda
     * (separador de miles, de decimales, posición del símbolo, etc.).
     *
     * @param float    $amount      Importe a formatear
     * @param int|null $idCurrency  ID de moneda, null = moneda activa del contexto
     * @return string  Ej: "1.234,56 €" o "$1,234.56"
     */
    public static function formatCurrency(float $amount, ?int $idCurrency = null): string
    {
        try {
            if ($idCurrency !== null) {
                $currency = new Currency((int) $idCurrency);
                // Si la moneda no existe, caer al contexto
                if (!Validate::isLoadedObject($currency)) {
                    $currency = Context::getContext()->currency;
                }
            } else {
                $currency = Context::getContext()->currency;
            }

            if (!$currency || !Validate::isLoadedObject($currency)) {
                // Último recurso: moneda por defecto de la tienda
                $currency = Currency::getDefaultCurrency();
            }

            return Tools::displayPrice($amount, $currency);
        } catch (Throwable $e) {
            // Fallback seguro: devolver el número con 2 decimales
            return number_format($amount, 2, '.', ',');
        }
    }

    // ===========================================================
    // MÉTODO 4: getOptimalGranularity
    // ===========================================================

    /**
     * Determina la granularidad óptima del gráfico según el rango de fechas seleccionado.
     *
     * Reglas:
     *  ≤ 31 días  → 'day'   (cada punto es un día)
     *  ≤ 90 días  → 'week'  (cada punto es una semana)
     *  > 90 días  → 'month' (cada punto es un mes)
     *
     * @param string $dateFrom  Fecha inicio en formato Y-m-d
     * @param string $dateTo    Fecha fin en formato Y-m-d
     * @return string 'day'|'week'|'month'
     */
    public static function getOptimalGranularity(string $dateFrom, string $dateTo): string
    {
        try {
            $from = new DateTime($dateFrom);
            $to   = new DateTime($dateTo);
            $days = (int) $from->diff($to)->days;

            if ($days <= 31) {
                return 'day';
            }

            if ($days <= 90) {
                return 'week';
            }

            return 'month';
        } catch (Throwable $e) {
            return 'day'; // fallback seguro
        }
    }
}
