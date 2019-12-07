<?php

namespace UonSoftware\LaraAuth\Http\Controllers;

use Throwable;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use UonSoftware\LaraAuth\Contracts\LoginContract;
use UonSoftware\LaraAuth\Http\Requests\LoginRequest;
use UonSoftware\LaraAuth\Exceptions\PasswordUpdateException;
use UonSoftware\LaraAuth\Exceptions\EmailIsNotVerifiedException;
use UonSoftware\LaraAuth\Exceptions\InvalidCredentialsException;

class LoginController extends Controller
{
    /**
     * @var \UonSoftware\LaraAuth\Contracts\LoginContract
     */
    private $loginService;


    /**
     * LoginController constructor.
     *
     * @param \UonSoftware\LaraAuth\Contracts\LoginContract $loginService
     */
    public function __construct(LoginContract $loginService)
    {
        $this->loginService = $loginService;
    }

    /**
     * @param \UonSoftware\LaraAuth\Http\Requests\LoginRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request): ?JsonResponse
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
