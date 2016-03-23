<?php

/**
 * Class for rendering the MyStyle Addons page within the WordPress
 * Administrator.
 * @package MyStyle
 * @since 0.1.16
 */
class MyStyle_Addons_Page {

    // class instance
    static $instance;

    /**
     * Constructor, constructs the designs page and adds it to the Settings
     * menu.
     */
    public function __construct() {
        add_action('admin_menu', array(&$this, 'add_page_to_menu'));
    }

    /**
     * Function to add the designs page to the MyStyle menu.
     */
    public function add_page_to_menu() {
        $mystyle_hook = 'mystyle';

        $hook = add_submenu_page(
                $mystyle_hook, 'Add-ons', 'Add-ons', 'manage_options', $mystyle_hook . '_addons', array($this, 'render_page')
        );
    }

    /**
     * Function to render the MyStyle Addons page.
     */
    public function render_page() {
        ?>
        <div class="wrap add-ons">
            <h2 class="mystyle-admin-title">
                <span id="icon-options-general" class="icon100"></span><span class="glyphicon glyphicon-plus-sign" style="float:right;"></span>
                MyStyle Add-ons
            </h2>

            <ul class="products">
                <li>
                    <a href="http://www.mystyleplatform.com/product/design-manager-mystyle-wordpress-plugin/" target="_blank">
                        <h3>MyStyle Design Manager</h3>
                        <img src="<?php echo MYSTYLE_ASSETS_URL . 'images/addons/design_manager.jpg' ?>" alt="Design Manager" />
                        <p>
                            The MyStyle Design Manager allows you to manage
                            the designs made by users from within the
                            WordPress administrator.  Get quick links to reload or delete designs.
                        </p>
                    </a>
                </li>
                <li>
                    <a href="http://www.mystyleplatform.com/product/edit-options-cart-woo-commerce-standalone-wordpress-plugin/" target="_blank">
                        <h3>Edit Options in Cart</h3>
                        <img src="<?php echo MYSTYLE_ASSETS_URL . 'images/addons/edit-options-in-cart-screenshot-1.jpg' ?>" alt="Edit Product Options" />
                        <p>
                            Our "Edit Options in Cart Plugin" allows users to change product options in the cart (and refreshes the page for new prices).
                            This is a standalone add-on for WooCommerce and does not require MyStyle.
                        </p>
                    </a>
                </li>
            </ul>
        </div>
        <div class="clear" style="clear:both; height: 40px;"></div>
        <div class="panel updated fade">
            <p><i>Need more add-ons, UIs, products, website upgrades, or services?  We have even more great things available in the <a href="http://www.mystyleplatform.com/marketplace" style="font-weight: bold;">MyStyle Marketplace</a>.</i></p>
        </div>
        <?php
    }

    /*
     * Singleton instance
     */

    public static function get_instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

}
