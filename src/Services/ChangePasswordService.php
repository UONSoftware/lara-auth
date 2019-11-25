<?php
declare(strict_types=1);

namespace UonSoftware\LaraAuth\Services;

use Tymon\JWTAuth\Manager as JwtManager;
use UonSoftware\LaraAuth\Dto\PasswordReset;
use UonSoftware\LaraAuth\Contracts\ChangePasswordContract;
use Tymon\JWTAuth\Validators\PayloadValidator;
use UonSoftware\LaraAuth\Exceptions\PasswordUpdateException;
use UonSoftware\LaraAuth\Contracts\UpdateUserPasswordContract;

/**
 * Class ChangePasswordService
 *
 * @package UonSoftware\LaraAuth\Services
 */
class ChangePasswordService implements ChangePasswordContract
{
    /**
     * @var UpdateUserPasswordContract
     */
    private $userPasswordContract;
    
    /**
     * @var \Tymon\JWTAuth\Manager
     */
    private $jwtManger;
    
    /**
     * @var \Tymon\JWTAuth\Contracts\Providers\JWT
     */
    private $jwtProvider;
    
    /**
     * @var \Tymon\JWTAuth\Validators\PayloadValidator
     */
    private $jwtPayloadValidator;
    
    public function __construct(
        JwtManager $jwtManager,
        PayloadValidator $jwtPayloadValidator,
        UpdateUserPasswordContract $userPasswordContract
    ) {
        
        $this->userPasswordContract = $userPasswordContract;
        $this->jwtManger = $jwtManager;
        $this->jwtPayloadValidator = $jwtPayloadValidator;
        $this->jwtProvider = $jwtManager->getJWTProvider();
    }
    
    /**
     * @inheritDoc
     *
     * @param  \UonSoftware\LaraAuth\Dto\PasswordReset  $passwordReset
     *
     * @throws \UonSoftware\LaraAuth\Exceptions\PasswordUpdateException
     */
    public function changePassword(PasswordReset $passwordReset): void
    {
        if (!$this->userPasswordContract->updatePassword($passwordReset->user->email, $passwordReset->password)) {
            throw new PasswordUpdateException();
        }
    }
}
