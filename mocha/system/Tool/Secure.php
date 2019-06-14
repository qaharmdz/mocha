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

namespace Mocha\System\Tool;

use Mocha\Controller;

class Secure extends Controller
{
    public function csrfToken()
    {
        return $this->user->fingerprint();
    }

    /**
     * Generate csrf form input.
     *
     * @return string
     */
    public function csrfField()
    {
        return '<input type="hidden" name="csrf-token" value="' . $this->csrfToken() . '" class="csrf-token" />';
    }

    /**
     * Validate post csrf form input.
     *
     * @return bool
     */
    public function csrfValidate()
    {
        if ($valid = $this->request->post->get('csrf-token') === $this->csrfToken()) {
            $this->request->post->remove('csrf-token');
        }

        return $valid;
    }
}
