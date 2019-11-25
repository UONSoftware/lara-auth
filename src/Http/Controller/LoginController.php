<?php

namespace UonSoftware\LaraAuth\Http\Controllers;

use Throwable;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Validator;
use UonSoftware\LaraAuth\Contracts\LoginContract;
use UonSoftware\LaraAuth\Http\Requests\LoginRequest;
use UonSoftware\LaraAuth\Exceptions\PasswordUpdateException;
use UonSoftware\LaraAuth\Exceptions\EmailIsNotVerifiedException;
use UonSoftware\LaraAuth\Exceptions\InvalidCredentialsException;

class LoginController extends Controller
{
    private $loginService;
    
    private $validator;
    
    public function __construct(LoginContract $loginService, Validator $validator)
    {
        $this->loginService = $loginService;
        $this->validator = $validator;
    }
    
    public function login(LoginRequest $request)
    {
        try {
            return response()->json($this->loginService->login($request->validated()));
        } catch (InvalidCredentialsException | PasswordUpdateException | EmailIsNotVerifiedException $e) {
            return response()->json(['message' => $e->getMessage()]);
        } catch (Throwable $e) {
            return response()->json(['message' => 'An error has occurred']);
        }
    }
}
