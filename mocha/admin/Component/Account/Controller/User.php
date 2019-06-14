<?php
/**
 * This file is part of Mocha.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * Released under GPL version 3 or any later version.
 * Copyright and license see LICENSE file or https://www.gnu.org/licenses/gpl-3.0.en.html.
 */

namespace Mocha\Admin\Component\Account\Controller;

use Mocha\Controller;

class User extends Controller
{
    /**
     * @see init::logout()
     */
    public function logout()
    {
        $this->user->logout();

        $this->session->flash->set('alert_logout', true);

        return $this->response->redirect($this->router->url());
    }
}
