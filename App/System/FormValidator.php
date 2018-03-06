<?php
namespace App\System;

use \App\Models\CategoriesModel;
use \App\Models\UsersModel;

class FormValidator {

    private $errors = [];

    public function notEmpty($element, $value, $message) {
        if(empty($value)) {
            $this->errors[$element] = $message;
            return false;
        }
        return true;
    }

    public function validCategory($element, $value, $message) {
        $model    = new CategoriesModel();
        $category = $model->find($value);
        if(!$category) {
            $this->errors[$element] = $message;
        }
    }

    public function samePassword($element, $value, $value_verification, $message) {
        if(empty($value) || ($value != $value_verification)) {
            $this->errors[$element] = $message;
        }
    }

    public function validPassword($element, $value, $message) {
        // At least 10 chars long
        if (strlen($value) < 10) {
            $this->errors[$element] = $message . "The password has to be at least 10 characters long.";
        }
        // At most 128 chars long
        else if (strlen($value) > 128) {
            $this->errors[$element] = $message . "The password has to be at most 128 characters long.";
        }
        // At least 1 uppercase
        else if (!preg_match('/[A-Z]/', $value)){
            $this->errors[$element] = $message . "The password has to contain at least 1 uppercase character.";
        }

        // At least 1 lowercase
        else if (!preg_match('/[a-z]/', $value)) {
            $this->errors[$element] = $message . "The password has to contain at least 1 lowercase character.";
        }
        // At least 1 digit
        else if (!preg_match('/\d/', $value)) {
            $this->errors[$element] = $message . "The password has to contain at least 1 digit.";
        }
        // Not more than 2 identical chars in a row
        else if ($this->identicalCharsInRow($value)) {
            $this->errors[$element] = $message . "The password can not contain more than two identical characters in a row.";
        }
    }

    public function identicalCharsInRow($string) {
        $identicalInRow = 0;
        for ($i = 1; $i < strlen($string); $i++) {
            if ($string[$i - 1] == $string[$i]) {
                $identicalInRow++;
                if ($identicalInRow > 1) {
                    break;
                }
            }
            else {
                $identicalInRow = 0;
            }
        }
        return $identicalInRow > 1;
    }

    public function validUsername($element, $value, $message) {
        if(!preg_match('/[a-z0-9]+/', $value)) {
            $this->errors[$element] = $message;
        }
    }

    public function availableUsername($element, $value, $message) {
        $model  = new UsersModel();
        $result = $model->query("SELECT * FROM users WHERE username = ?", [
            $value
        ], true);

        if($result) {
            $this->errors[$element] = $message;
        }
    }

    public function validEmail($element, $value, $message) {
        if (!empty($value)){
            if(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $this->errors[$element] = $message;
            }
        }
    }
    
    public function isNumeric($element, $value, $message) {
        if(!is_numeric($value)) {
            $this->errors[$element] = $message;
        }
    }

    public function isInteger($element, $value, $message) {
        if(!is_int(intval($value))) {
            $this->errors[$element] = $message;
        }
    }

    public function validImage($element, $value, $message) {
        if(empty($value)) {
            $this->errors[$element] = $message;
        }

        else {
            if(empty($value['type'])) {
                $this->errors[$element] = $message;
                return;
            }

            if($value['size'] > 1000000) {
                $this->errors[$element] = "Your media is too big (> 1Mo)";
                return;
            }
        }
    }

    public function isValid() {
        if(empty($this->errors)) return true;
        else return false;
    }

    public function getErrors() {
        return $this->errors;
    }

}
