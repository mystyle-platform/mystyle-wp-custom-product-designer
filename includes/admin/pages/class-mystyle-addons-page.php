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
        add_action( 'admin_menu', array( &$this, 'add_page_to_menu' ) );
    }

    /**
     * Function to add the designs page to the MyStyle menu.
     */
    public function add_page_to_menu() {
        $mystyle_hook = 'mystyle';

        $hook = add_submenu_page(
                $mystyle_hook,
                'Add-ons',
                'Add-ons',
                'manage_options',
                $mystyle_hook . '_addons',
                array( $this, 'render_page' )
        );
    }

    /**
     * Function to render the MyStyle Addons page.
     */
    public function render_page() {
        ?>
            <style>
                ul.products {
                    margin-top: 3em;
                }
                li a h3 {
                    background: #fff none repeat scroll 0 0;
                    margin: 0 !important;
                    padding: 20px !important;
                    color: #23282d;
                    font-size: 1.3em;
                    margin: 1em 0;
                    border-bottom: 1px solid #f1f1f1;
                }
                ul.products li a {
                    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
                    display: block;
                    text-align: center;
                    text-decoration: none;
                    width: 300px;
                    background: #f5f5f5 none repeat scroll 0 0;
                    border: 1px solid #ddd;
                }
                ul.products li a:hover {
                    background: #ffffff none repeat scroll 0 0;
                }
                ul.products li a img,
                ul.products li a p {
                    margin: 1em;
                }
                ul.products li a p {
                    font-family: "Open Sans",sans-serif;
                    color: #444;
                    border-top: 1px solid #f1f1f1;
                    margin-top: 0;
                    padding-top: 1em;
                }
                ul.products li img {
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
                }
            </style>

            <div class="wrap">
                <h2 class="mystyle-admin-title">
                    <div id="icon-options-general" class="icon100"></div>
                    MyStyle Add-ons
                </h2>

                <ul class="products">
                    <li>
                        <a href="http://www.mystyleplatform.com/product/design-manager-mystyle-wordpress-plugin/" target="_blank">
                            <h3>MyStyle Design Manager</h3>
                            <img width="200" height="142" src="<?php echo MYSTYLE_ASSETS_URL . 'images/addons/design_manager.jpg'?>" alt="Design Manager" />
                            <p>
                                The MyStyle Design Manager allows you to manage
                                the designs made by users from within the
                                WordPress administrator.
                            </p>
                        </a>
                    </li>
                </ul>
            </div>
        <?php
    }

    /*
     * Singleton instance
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

}