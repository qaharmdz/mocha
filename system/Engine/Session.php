<?php
/*
 * This file is part of the Mocha package.
 *
 * (c) Mudzakkir <qaharmdz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mocha\System\Engine;

use Symfony\Component\HttpFoundation;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

class Session extends HttpFoundation\Session\Session
{
    /**
     * Access to flash bag
     *
     * @var \Symfony\Component\HttpFoundation\Session\Flash\Flashbag
     */
    public $flash;

    public function __construct(SessionStorageInterface $storage = null, AttributeBagInterface $attributes = null, FlashBagInterface $flashes = null)
    {
        parent::__construct($storage, $attributes, $flashes);

        $this->flash = $this->getFlashBag();
    }

    public function setOptions(array $options)
    {
        if ($this->storage instanceof NativeSessionStorage) {
            $this->storage->setOptions($options);
        }
    }
}
