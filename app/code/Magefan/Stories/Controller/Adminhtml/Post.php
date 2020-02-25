<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Stories\Controller\Adminhtml;

/**
 * Admin Stories post edit controller
 */
class Post extends Actions
{
	/**
	 * Form session key
	 * @var string
	 */
    protected $_formSessionKey  = 'stories_post_form_data';

    /**
     * Allowed Key
     * @var string
     */
    protected $_allowedKey      = 'Magefan_Stories::stories_post';

    /**
     * Model class name
     * @var string
     */
    protected $_modelClass      = 'Magefan\Stories\Model\Post';

    /**
     * Active menu key
     * @var string
     */
    protected $_activeMenu      = 'Magefan_Stories::post';

    /**
     * Status field name
     * @var string
     */
    protected $_statusField     = 'is_active';

    /**
     * Save request params key
     * @var string
     */
    protected $_paramsHolder 	= 'post';
}
