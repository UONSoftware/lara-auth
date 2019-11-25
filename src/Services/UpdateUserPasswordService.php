<?php


namespace UonSoftware\LaraAuth\Services;


use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Hashing\Hasher;
use UonSoftware\LaraAuth\Events\PasswordChangedEvent;
use App\Exceptions\NullReferenceException;
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
     * UpdateUserPasswordService constructor.
     *
     * @param  \Illuminate\Contracts\Hashing\Hasher  $hasher
     * @param  \Illuminate\Contracts\Events\Dispatcher  $eventDispatcher
     */
    public function __construct(Hasher $hasher, EventDispatcher $eventDispatcher)
    {
        $this->hasher = $hasher;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param  \App\User|integer  $user
     * @param  string  $newPassword
     *
     * @return bool
     * @throws \App\Exceptions\NullReferenceException
     * @throws \Throwable
     */
    public function updatePassword($user, string $newPassword): bool
    {
        if (is_null($user)) {
            throw new NullReferenceException('User reference cannot be null');
        }

        $hash = $this->hasher->make($newPassword);

        if ($user instanceof User) {
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

        if (User::query()->where($field, '=', $user)->update(['password' => $hash]) === 0) {
            DB::rollBack();
            return false;
        }

        DB::commit();

        $this->eventDispatcher->dispatch(new PasswordChangedEvent([
            'field' => $field,
            'value' => $user,
        ]));

        return true;
    }
}
