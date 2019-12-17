<?php

namespace UonSoftware\LaraAuth\Http\Controllers;

use Throwable;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use UonSoftware\LaraAuth\Contracts\LoginContract;
use UonSoftware\LaraAuth\Http\Requests\LoginRequest;
use UonSoftware\LaraAuth\Exceptions\PasswordUpdateException;
use UonSoftware\RefreshTokens\Exceptions\InvalidRefreshToken;
use UonSoftware\RefreshTokens\Exceptions\RefreshTokenExpired;
use UonSoftware\RefreshTokens\Exceptions\RefreshTokenNotFound;
use UonSoftware\RefreshTokens\Contracts\RefreshTokenRepository;
use UonSoftware\LaraAuth\Exceptions\EmailIsNotVerifiedException;
use UonSoftware\LaraAuth\Exceptions\InvalidCredentialsException;
use UonSoftware\LaraAuth\Http\Requests\RevokeRefreshTokenRequest;

class LoginController extends Controller
{
    /**
     * @var \UonSoftware\LaraAuth\Contracts\LoginContract
     */
    private $loginService;

    /**
     * @var \UonSoftware\RefreshTokens\Contracts\RefreshTokenRepository
     */
    private $refreshTokenRepository;


    /**
     * LoginController constructor.
     *
     * @param \UonSoftware\LaraAuth\Contracts\LoginContract               $loginService
     * @param \UonSoftware\RefreshTokens\Contracts\RefreshTokenRepository $refreshTokenRepository
     */
    public function __construct(LoginContract $loginService, RefreshTokenRepository $refreshTokenRepository)
    {
        $this->loginService = $loginService;
        $this->refreshTokenRepository = $refreshTokenRepository;
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
            return response()->json(['message' => $e->getMessage(), 401]);
        } catch (Throwable $e) {
            return response()->json(['message' => 'An error has occurred'], 500);
        }
    }

    public function revokeRefreshToken(RevokeRefreshTokenRequest $request)
    {
        try {
            $userPrimaryKey = config('refresh_token.user.id');
            $userId = $request->user()->{$userPrimaryKey};
            $refreshToken = $request->input('refresh_token');
            $isDeleted = $this->refreshTokenRepository->revokeToken($refreshToken, $userId);

            if($isDeleted === true) {
                return response()->json(null, 204);
            }
            return response()->json(['message' => 'Refresh token doesn\'t belong to you'], 401);
        } catch (InvalidRefreshToken $e) {
            return response()->json(['message' => 'Refresh token is invalid'], 403);
        } catch (RefreshTokenExpired | RefreshTokenNotFound $e) {
            return response()->json(['message' => 'Refresh token is already deleted due to expiration'], 404);
        } catch (Throwable $e) {
            return response()->json(['message' => 'An error has occurred'], 500);
        }

    }
}
