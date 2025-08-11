<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Dni implements Rule
{
    private const VALID_DNI_PATTERN = '/^[XYZ\d]\d{7,7}[^UIOÑ\d]$/u';
    private const CONTROL_LETTER_MAP = 'TRWAGMYFPDXBNJZSQVHLCKE';
    private const NIE_INITIAL_LETTERS = ['X', 'Y', 'Z'];
    private const NIE_INITIAL_REPLACEMENTS = ['0', '1', '2'];
    private const DIVISOR = 23;

    private const VALID_CIF_LETTERS = '/^[ABCDEFGHJNPQRSUVW]{1}/';
    private const CIF_CODES = 'JABCDEFGHI';
    private const CIF_NUMERIC = ['A', 'B', 'E', 'H'];
    private const CIF_LETTERS = ['K', 'P', 'Q', 'S'];

    private const VALID_NIE_PATTERN = '/^[XYZT][0-9][0-9][0-9][0-9][0-9][0-9][0-9][A-Z0-9]/';
    private const NIE_LETTERS = 'TRWAGMYFPDXBNJZSQVHLCKE';

    private function checkIsValidDni(string $dni): bool
    {
        return preg_match(self::VALID_DNI_PATTERN, $dni);
    }

    private function calculateModulus(string $dni): int
    {
        $numeric = substr($dni, 0, -1);
        $number = (int)str_replace(self::NIE_INITIAL_LETTERS, self::NIE_INITIAL_REPLACEMENTS, $numeric);

        return $number % self::DIVISOR;
    }

    private function checkIsValidCif(string $cif): bool
    {
        if (strlen($cif) != 9) { return false; }

        $sum = (string) $this->calculateCifSum($cif);
        $n = (10 - substr ($sum, -1)) % 10;


        if (preg_match(self::VALID_CIF_LETTERS, $cif))
        {
            if (in_array ($cif[0], self::CIF_NUMERIC)) {
                return ($cif[8] == $n);
            } else if (in_array($cif[0], self::CIF_LETTERS)) {
                return ($cif[8] == self::CIF_CODES[$n]);
            } else if (is_numeric($cif[8])) {
                return ($cif[8] == $n);
            } else {
                return ($cif[8] == self::CIF_CODES[$n]);
            }
        }

        return false;
    }

    private function calculateCifSum(string $cif)
    {
        $sum = intval($cif[2]) + intval($cif[4]) + intval($cif[6]);

        for ($i = 1; $i < 8; $i += 2) {
            $tmp = (string) (2 * intval($cif[$i]));
            $tmp = $tmp[0] + ((strlen ($tmp) == 2) ?  $tmp[1] : 0);
            $sum += $tmp;
        }

        return $sum;
    }

    private function checkIsValidNie($nie)
    {
        if (strlen($nie) != 9) { return false; }

        if (preg_match(self::VALID_NIE_PATTERN, $nie))
        {
            for ($i = 0; $i < 9; $i ++)
            {
                $num[$i] = substr($nie, $i, 1);
            }

            if ($num[8] == substr(self::NIE_LETTERS, substr(str_replace(['X', 'Y', 'Z'], ['0', '1', '2'], $nie), 0, 8) % 23, 1))
            {
                return true;
            } else {
                return false;
            }
        }
    }


    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $document_number
     * @return bool
     */
    public function passes($attribute, $document_number)
    {
        $valid = false;

        $document_number = strtoupper($document_number);

        // Check for DNI

        if ($this->checkIsValidDni($document_number))
        {
            $mod = $this->calculateModulus($document_number);

            $letter = substr($document_number, -1);

            if ($letter === self::CONTROL_LETTER_MAP[$mod])
            {
                $valid = true;
            }
        }

        if (!$valid && $this->checkIsValidCif($document_number))
        {
            $valid = true;
        }

        if (!$valid && $this->checkIsValidNie($document_number))
        {
            $valid = true;
        }

        return $valid;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'validation.format';
    }
}
