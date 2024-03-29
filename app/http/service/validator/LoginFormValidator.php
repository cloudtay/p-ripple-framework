<?php

namespace app\http\service\validator;


use Cclilshy\PRipple\Framework\Facades\Route;
use Cclilshy\PRipple\Framework\Interface\ValidatorInterface;
use Override;

/**
 * Class LoginFormValidate
 */
class LoginFormValidator implements ValidatorInterface
{

    #[Override] public function method(): string|array
    {
        return [Route::GET, Route::POST];
    }

    #[Override] public function rules(): array
    {
        return [
            'username' => 'required',
            'password' => 'required'
        ];
    }

    #[Override] public function messages(): array
    {
        return [
            'username.required' => 'Please enter your username',
            'password.required' => 'Please enter your password'
        ];
    }
}

