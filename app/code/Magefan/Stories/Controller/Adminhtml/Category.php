<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Stories\Controller\Adminhtml;

/**
 * Admin stories category edit controller
 */
class Category extends Actions
{
	/**
	 * Form session key
	 * @var string
	 */
    protected $_formSessionKey  = 'stories_category_form_data';

    /**
     * Allowed Key
     * @var string
     */
    protected $_allowedKey      = 'Magefan_Stories::stories_category';

    /**
     * Model class name
     * @var string
     */
    protected $_modelClass      = 'Magefan\Stories\Model\Category';

    /**
     * Active menu key
     * @var string
     */
    protected $_activeMenu      = 'Magefan_Stories::category';

    /**
     * Status field name
     * @var string
     */
    protected $_statusField     = 'is_active';
}
