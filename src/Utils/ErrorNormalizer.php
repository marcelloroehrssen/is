<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 12/05/2018
 * Time: 02:15
 */

namespace App\Utils;


use Symfony\Component\Form\FormError;

class ErrorNormalizer
{
    public function normalizeInvalidCredential($e)
    {
        if (empty($e)) {
            return;
        }

        return [
            [
                'key' => $e->getMessage(),
                'data' => []
            ]
        ];
    }

    public function normalizeForErrors($violations)
    {
        if (empty($violations)) {
            return;
        }

        $error = [];
        foreach ($violations as $violation) {
            $error[] = [
                'key' => $violation->getMessage(),
                'data' => []
            ];
        }
        return $error;
    }
}