<?php


namespace UonSoftware\LaraAuth\Http\Controller;


use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class RegisterController extends Controller
{
    public function register(): JsonResponse
    {
        return response()->json(['message' => 'Method not implemented'], 500);
    }
}