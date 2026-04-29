<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Model;

class StringHelpers
{
    /**
     * Decrypt the given value.
     *
     * @param  string  $payload
     * @param  bool  $unserialize
     * @return mixed
     *
     * @throws \Illuminate\Contracts\Encryption\DecryptException
     */
    public static function modelDecrypt(
        string $payload,
        ?bool $unserialize = false,
    ): mixed {
        return Model::currentEncrypter()->decrypt($payload, $unserialize ?? false);
    }

    /**
     * Encrypt the given value.
     *
     * @param  mixed  $value
     * @param  bool  $serialize
     * @return string
     *
     * @throws \Illuminate\Contracts\Encryption\EncryptException
     */
    public static function modelEncrypt(#[\SensitiveParameter] $value, ?bool $serialize = false): string
    {
        return Model::currentEncrypter()->encrypt($value, $serialize ?? false);
    }
}
