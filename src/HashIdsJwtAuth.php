<?php


namespace UonSoftware\LaraAuth;


use Hashids\HashidsInterface;
use Illuminate\Contracts\Auth\Guard as GuardContract;
use Tymon\JWTAuth\Providers\Auth\Illuminate as JwtAuth;

/**
 * Class HashidsJwtAuth
 *
 * @package UonSoftware\LaraAuth
 */
class HashidsJwtAuth extends JwtAuth
{
    /**
     * @var \Hashids\HashidsInterface
     */
    protected $hashids;

    /**
     * HashidsJwtAuth constructor.
     *
     * @param \Hashids\HashidsInterface        $hashids
     * @param \Illuminate\Contracts\Auth\Guard $auth
     */
    public function __construct(HashidsInterface $hashids, GuardContract $auth)
    {
        parent::__construct($auth);
        $this->hashids = $hashids;
    }

    /**
     * @param string|int $id
     *
     * @return mixed
     */
    public function byId($id)
    {
        $ids = $this->hashids->decodeHex($id);
        return parent::byId($ids);
    }
}
