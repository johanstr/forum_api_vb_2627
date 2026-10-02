<?php
namespace App\Http\Controllers;

abstract class Controller
{
   // Controleert de data met regels zoals 'required|string|max:100'.
   // $partial = true bij PATCH: dan controleren we alleen de meegestuurde velden.
   // Geeft een array met foutmeldingen terug (leeg = alles in orde).
   protected function validate(array $data, array $rules, bool $partial = false): array
   {
      $errors = [];

      // Bij PATCH moet er minstens één bekend veld zijn meegestuurd
      if ($partial && count(array_intersect_key($data, $rules)) === 0) {
         $errors['body'] = 'Stuur minstens één van deze velden mee: ' . implode(', ', array_keys($rules)) . '.';

         return $errors;
      }

      foreach ($rules as $field => $ruleString) {
         $fieldRules = explode('|', $ruleString);
         $isFilled = isset($data[$field]) && $data[$field] !== '';

         // Veld ontbreekt of is leeg
         if (!$isFilled) {
            $isMissingInPatch = $partial && !array_key_exists($field, $data);

            if (in_array('required', $fieldRules) && !$isMissingInPatch) {
               $errors[$field] = "Het veld {$field} is verplicht.";
            }

            continue;
         }

         $value = $data[$field];

         foreach ($fieldRules as $rule) {
            if ($rule === 'string' && !is_string($value)) {
               $errors[$field] = "Het veld {$field} moet tekst zijn.";
               break;
            }

            if ($rule === 'int' && filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
               $errors[$field] = "Het veld {$field} moet een positief geheel getal zijn.";
               break;
            }

            if (str_starts_with($rule, 'max:')) {
               $max = (int) substr($rule, 4);

               if (is_string($value) && mb_strlen($value) > $max) {
                  $errors[$field] = "Het veld {$field} mag maximaal {$max} tekens bevatten.";
                  break;
               }
            }

            // Bijv. 'exists:App\Models\ThreadModel': het id moet in die tabel bestaan.
            // Zet deze regel altijd na 'int', zodat de waarde al een geldig getal is.
            if (str_starts_with($rule, 'exists:')) {
               $modelClass = substr($rule, 7);

               if ($modelClass::find((int) $value) === null) {
                  $errors[$field] = "Het veld {$field} verwijst naar id {$value}, maar dat bestaat niet.";
                  break;
               }
            }
         }
      }

      return $errors;
   }
}
