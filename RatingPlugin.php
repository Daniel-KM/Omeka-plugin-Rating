<?php
/**
 * Rating
 *
 * Add a widget to allow users to rate a record instantly.
 *
 * @copyright Copyright Daniel Berthereau, 2014
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt
 * @package Rating
 *
 * @todo Use mixin_owner
 */

/**
 * The Rating plugin.
 * @package Omeka\Plugins\Rating
 */
class RatingPlugin extends Omeka_Plugin_AbstractPlugin
{
    /**
     * @var array Hooks for the plugin.
     */
    protected $_hooks = array(
        'initialize',
        'install',
        'uninstall',
        'config_form',
        'config',
        'define_acl',
        'admin_head',
        'public_head',
        'admin_items_show_sidebar',
        'admin_items_browse_simple_each',
        'admin_items_browse_detailed_each',
        'admin_items_browse',
        'public_items_show',
        'public_items_browse_each',
        'public_items_browse',
        'after_delete_item',
    );

    /**
     * @var array Options and their default values.
     */
    protected $_options = array(
        // Use the hook or not.
        'rating_add_to_items_show' => true,
        'rating_add_to_items_browse' => true,
        // Without roles.
        'rating_public_allow_rate' => true,
        // With roles, in particular if Guest User is installed.
        // serialize(array()) = 'a:0:{}'.
        'rating_add_roles' => 'a:0:{}',
        // Privacy settings (can be: "anonymous", "hashed" or "clear").
        'rating_privacy' => 'hashed',
    );

    /**
     * Add the translations.
     */
    public function hookInitialize()
    {
        add_translation_source(dirname(__FILE__) . '/languages');
        if (version_compare(OMEKA_VERSION, '2.2', '>=')) {
            add_shortcode('rating', array($this, 'shortcodeRatingWidget'));
        }
    }

    /**
     * Install the plugin.
     */
    public function hookInstall()
    {
        $db = $this->_db;
        $sql = "
        CREATE TABLE IF NOT EXISTS `$db->Rating` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `record_type` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
            `record_id` int(10) unsigned NOT NULL,
            `score` decimal(5, 2) unsigned NULL,
            `user_id` int(11) DEFAULT NULL,
            `ip` tinytext COLLATE utf8_unicode_ci NOT NULL,
            `user_agent` tinytext COLLATE utf8_unicode_ci,
            `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `record_type_record_id` (`record_type`, `record_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ";
        $db->query($sql);

        $this->_installOptions();
    }

    /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    {
        $db = $this->_db;
        $sql = "DROP TABLE IF EXISTS `$db->Rating`";
        $db->query($sql);

        $this->_uninstallOptions();
    }

    /**
     * Shows plugin configuration page.
     */
    public function hookConfigForm()
    {
        $view = get_view();
        echo $view->partial(
            'plugins/rating-config-form.php', array(
                'view' => $view,
        ));
    }

    /**
     * Saves plugin configuration page.
     *
     * @param array Options set in the config form.
     */
    public function hookConfig($args)
    {
        $post = $args['post'];
        foreach (array(
                'rating_add_roles',
            ) as $posted) {
            $post[$posted] = isset($post[$posted])
                ? serialize($post[$posted])
                : serialize(array());
        }
        foreach ($post as $key => $value) {
            set_option($key, $value);
        }
    }

    /**
     * Defines the plugin's access control list.
     *
     * @param object $args
     */
    public function hookDefineAcl($args)
    {
        $acl = $args['acl'];
        $acl->addResource('Rating_Rating');
        $acl->allow(null, 'Rating_Rating', array('show', 'add'));

        if (get_option('rating_public_allow_rate')) {
            $acl->allow(null, 'Rating_Rating', array('add'));
        }
        else {
            $roles = unserialize(get_option('rating_add_roles'));
            // Check that all the roles exist, in case a plugin-added role has
            // been removed (e.g. GuestUser).
            foreach ($roles as $role) {
                if ($acl->hasRole($role)) {
                    $acl->allow($role, 'Rating_Rating', array('add'));
                }
            }
        }
    }

    public function hookAdminHead($args)
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        if ($controller == 'items'
                && ($action == 'show' || $action == 'browse')
            ) {
            queue_css_file('rating');
            queue_js_file('RateIt/jquery.rateit.min');
        }
    }

    public function hookPublicHead($args)
    {
        $itemsShow = (boolean) get_option('rating_add_to_items_show');
        $itemsBrowse = (boolean) get_option('rating_add_to_items_browse');
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        if ($controller == 'items'
                && (($itemsShow && ($action == 'show'))
                    || ($itemsBrowse && ($action == 'browse')))
            ) {
            queue_css_file('rating');
            queue_js_file('RateIt/jquery.rateit.min');
        }
    }

    public function hookAdminItemsShowSidebar($args)
    {
        $view = $args['view'];
        $item = $args['item'];

        $html = '<div class="panel">';
        $html .= '<h4>' . __('Rating') . '</h4>';
        $html .= $view->getRatingWidget($item, array('score', 'my rate'));
        $html .= '</div>';
        $html .= $view->partial('common/rating-js.php');

        echo $html;
    }

    public function hookAdminItemsBrowseSimpleEach($args)
    {
        $view = $args['view'];
        $item = $args['item'];

        echo $view->getRatingWidget($item, array('score'));
    }

    public function hookAdminItemsBrowseDetailedEach($args)
    {
        $view = $args['view'];
        $item = $args['item'];

        echo $view->getRatingWidget($item, array('my rate'));
    }

    /**
     * Hook for items/browse.
     *
     * Add the specific javascript.
     */
    public function hookAdminItemsBrowse($args)
    {
        $view = $args['view'];
        echo $view->partial('common/rating-js.php');
    }

    public function hookPublicItemsShow($args)
    {
        $view = $args['view'];
        $item = $args['item'];

        if (get_option('rating_add_to_items_show')) {
            $display = is_allowed('Rating_Rating', 'add')
                ? array('score text', 'my rate')
                : array('score');
            echo $view->getRatingWidget($item, $display);
            echo $view->partial('common/rating-js.php');
        }
    }

    public function hookPublicItemsBrowseEach($args)
    {
        $view = $args['view'];
        $item = $args['item'];

        if (get_option('rating_add_to_items_browse')) {
            echo $view->getRatingWidget($item, array('score'));
        }
    }

    public function hookPublicItemsBrowse($args)
    {
        $view = $args['view'];

        if (get_option('rating_add_to_items_browse')) {
            echo $view->partial('common/rating-js.php');
        }
    }

    /**
     * Hook used when an item is removed.
     */
    public function hookAfterDeleteItem($args)
    {
        $item = $args['item'];
        $record = $item;
        $ratings = $this->_db->getTable('Rating')->findByRecord($record);
        foreach ($ratings as $rating) {
            $rating->delete();
        }
    }

    /**
     * Shortcode for adding a rating widget.
     *
     * @param array $args
     * @param Omeka_View $view
     * @return string
     */
    public function shortcodeRatingWidget($args, $view)
    {
        // Check required arguments.
        if (!isset($args['record_type']) || !isset($args['record_id'])) {
            return;
        }
        $record = get_record_by_id($args['record_type'], $args['record_id']);
        if (!$record) {
            return;
        }

        // Get display values.
        $display = isset($args['display'])
            ? array_filter(array_map('trim', explode(',', $args['display'])))
            : array();

        // Add css and javascript to widget.
        $html = '<link rel="stylesheet" type="text/css" href="' . html_escape(src('rating', 'css', 'css')) . '">';
        $html .= $view->getRatingWidget($record, $display);
        $html .= common('rating-js');
        $html .= js_tag('RateIt/jquery.rateit.min');

        return $html;
    }

    /**
     * Get remote ip address. This check respects privacy settings.
     *
     * @todo Consolidate this function (see Rating Plugin, Rating model, Ajax controller, getRatingWidget).
     *
     * @return string
     */
    protected function _getRemoteIP()
    {
        $privacy = get_option('rating_privacy');
        if ($privacy == 'anonymous') {
            return '';
        }

        // Check if user is behind nginx.
        $ip = isset($_SERVER['HTTP_X_REAL_IP'])
            ? $_SERVER['HTTP_X_REAL_IP']
            : $_SERVER['REMOTE_ADDR'];

        return $privacy == 'clear'
            ? $ip
            : md5($ip);
    }
}
