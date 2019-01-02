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

use Symfony\Component\HttpFoundation\ParameterBag;

class Secure
{
    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    public $param;

    public function __construct(ParameterBag $bag)
    {
        $this->param = $bag;
        $this->param->set('hash_type', 'sha256');
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

    public function hash(string $string, string $type = '')
    {
        $type = in_array($type, hash_algos()) ? $type : $this->param->get('hash_type', 'sha256');

        return hash($type, $string, false);
    }

    /**
     * Generate 'random' code.
     *
     * @see https://github.com/bcit-ci/CodeIgniter/blob/develop/system/helpers/string_helper.php
     *
     * @param  string      $type
     * @param  int|integer $length
     *
     * @return string
     */
    public function generateCode(string $type = 'alnum', int $length = 16)
    {
        switch ($type) {
            case 'basic':
                $result = mt_rand();
                break;

            case 'alnum':
            case 'numeric':
            case 'alpha':
                switch ($type) {
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

        return substr($result, rand(0, (strlen($result) - $length)), $length);
    }
}
