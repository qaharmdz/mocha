<?php
/*
 * This file is part of Mocha package.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * Released under GPL version 3 or any later version.
 * Full copyright and license see LICENSE file or visit https://www.gnu.org/licenses/gpl-3.0.en.html.
 */

namespace Mocha\System\Library;

class Secure
{
    protected $param = [];

    /**
     * @param   hash_type
     */
    public function __construct(array $param = [])
    {
        $this->param = array_merge(['hash_type' => 'sha256'], $param);
    }

    public function password(string $password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function isValidPassword(string $password, string $hash)
    {
        return password_verify($password, $hash);
    }

    public function isPasswordNeedRehash(string $hash)
    {
        return password_needs_rehash($hash, PASSWORD_DEFAULT);
    }

    public function setHash($type)
    {
        $this->param['hash_type'] = in_array($type, hash_algos()) ? $type : 'sha256';
    }

    public function hash(string $string, string $type = '')
    {
        $type = in_array($type, hash_algos()) ? $type : $this->param['hash_type'];

        return hash($type, $string, false);
    }

    public function encode(string $string)
    {
        return base64_encode($string);
    }

    public function decode(string $string)
    {
        return base64_decode($string);
    }

    /**
     * Based on Codeigniter v3.1.0 helper/string_helper
     */
    public function randCode(string $type = 'alnum', int $length = 16)
    {
        switch ($type)
        {
            case 'basic':
                $result = mt_rand();
                break;
            case 'alnum':
            case 'numeric':
            case 'alpha':
                switch ($type)
                {
                    case 'alpha':
                        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'alnum':
                        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'numeric':
                        $pool = '0123456789';
                        break;
                }
                $result = str_shuffle(str_repeat($pool, ceil($length / strlen($pool))));
                break;
            case 'hash':
                $result = $this->hash(uniqid(mt_rand(), true));
                break;
        }

        return substr($result, rand(0, (strlen($result)-$length)), $length);
    }
}
