<?php


namespace UonSoftware\LaraAuth\Services;


use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Config\Repository as Config;
use UonSoftware\LaraAuth\Events\PasswordChangedEvent;
use UonSoftware\LaraAuth\Exceptions\NullReferenceException;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use UonSoftware\LaraAuth\Contracts\UpdateUserPasswordContract;

/**
 * Class UpdateUserPasswordService
 *
 * @package UonSoftware\LaraAuth\Services
 */
class UpdateUserPasswordService implements UpdateUserPasswordContract
{
    /**
     * @var \Illuminate\Contracts\Hashing\Hasher
     */
    private $hasher;

    /**
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    private $eventDispatcher;

    /**
     * @var \Illuminate\Config\Repository
     */
    private $config;

    /**
     * UpdateUserPasswordService constructor.
     *
     * @param \Illuminate\Contracts\Hashing\Hasher    $hasher
     * @param \Illuminate\Contracts\Events\Dispatcher $eventDispatcher
     * @param \Illuminate\Config\Repository           $config
     */
    public function __construct(Hasher $hasher, EventDispatcher $eventDispatcher, Config $config)
    {
        $this->hasher = $hasher;
        $this->eventDispatcher = $eventDispatcher;
        $this->config = $config;
    }

    /**
     * @throws \Throwable
     * @throws NullReferenceException
     *
     * @param integer|string $user
     * @param string         $newPassword
     *
     * @return bool
     */
    public function updatePassword($user, string $newPassword): bool
    {
        if ($user === null) {
            throw new NullReferenceException('User reference cannot be null');
        }

        $hash = $this->hasher->make($newPassword);
        $userModel = $this->config->get('lara_auth.user_model');
        if ($user instanceof $userModel) {
            $user->password = $hash;
            $user->saveOrFail();
            $this->eventDispatcher->dispatch(new PasswordChangedEvent($user));
            return true;
        }

        $field = 'id';

        if (is_string($user)) {
            $field = 'email';
        }

        DB::beginTransaction();

        $count = $userModel::query()
            ->where($field, '=', $user)
            ->update(['password' => $hash]);

        if ($count === 0) {
            DB::rollBack();
            return false;
        }

        DB::commit();

        $this->eventDispatcher->dispatch(
            new PasswordChangedEvent(
                [
                    'field' => $field,
                    'value' => $user,
                ]
            )
        );

        return true;
    }
}
