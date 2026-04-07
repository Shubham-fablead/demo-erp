<?php

if (!function_exists('parseGstJson')) {
    function parseGstJson($json)
    {
        $igst = $cgst = $sgst = 0;

        if (empty($json)) return compact('igst','cgst','sgst');

        if (is_array($json)) {
            $taxes = $json;
        } elseif (is_string($json)) {
            $taxes = json_decode($json, true);
        } else {
            return compact('igst','cgst','sgst');
        }

        if (!is_array($taxes)) return compact('igst','cgst','sgst');

        foreach ($taxes as $tax) {
            $name   = strtoupper($tax['tax_name'] ?? $tax['name'] ?? '');
            $amount = (float) ($tax['tax_amount'] ?? $tax['amount'] ?? 0);

            if ($name === 'IGST') $igst += $amount;
            if ($name === 'CGST') $cgst += $amount;
            if ($name === 'SGST') $sgst += $amount;
        }

        return compact('igst','cgst','sgst');
    }
}
