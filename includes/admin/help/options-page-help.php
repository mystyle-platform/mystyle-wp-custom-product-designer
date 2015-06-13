<?php
/**
 * Functions for rendering the MyStyle contextual help for the options page 
 * within the WordPress Administrator.
 * @package MyStyle
 * @since 0.1.0
 */
    
/**
 * Add help for the mystyle options page into the WordPress admin help system.
 * @param string $contextual_help The default contextual help that our 
 * function is going to replace.
 * @param string $screen_id Used to identify the page that we are on.
 * @param string $screen Used to access the elements of the current page.
 * @return string The new contextual help.
 */
function mystyle_options_page_help( $contextual_help, $screen_id, $screen ) {
    $overview_content = '
    <h1>MyStyle Custom Product Designer Help</h1>
    <p>Need help using the MyStyle Custom Product Designer plugin? Use the tabs
       to the left to find instructions for installation, use and 
       troubleshooting.
    </p>';

    $installation_content = '
    <h2>Installation/Configuration</h2>
    <p>
        The MyStyle Custom Product Designer is a simple plugin that allows your
        customers to customize your WooCommerce products.
    </p>
    <ol>
        <li>Install the plugin.</li>
        <li>Activate the plugin.</li>
        <li>Create an account at <a href="http://www.mystyleplatform.com/apply-mystyle-license-developer-account-api-key-secret/?ref=wp_plugin_4" target="_blank" title="mystyleplatform.com">mystyleplatform.com</a>.</li>
        <li>Get your MyStyle API Key and Secret and add them to the fields on this page.</li>
        <li>Install the WooCommerce Plugin (if not already installed).</li>
        <li>Create a new WooCommerce product or edit an existing one.</li>
        <li>In the product options, click on the MyStyle tab.</li>
        <li>Enable Customization for the product and enter the product\'s
            MyStyle Template Id.
        </li>
        <li>The product should now have a "Customize" button that takes the 
        user to the Customize page.</li>
    </ol>
    ';
    /*
    $use_content = '
    <h2>Using MyStyle</h2>
    <p>Once you have MyStyle set up and working...';
     */

    $troubleshooting_content = '
    <h2>Troubleshooting</h2>
    <p>If the plugin isn\'t working, please check the following:</p>
    <ul>
        <li>Make sure that the MyStyle plugin is installed and activated.</li>
        <li>Make sure that you have entered your MyStyle API Key and Secret
        on the MyStyle settings page.</li>
        <li>Make sure that WooCommerce is installed.</li>
        <li>Make sure that you have enabled customization on at least one of
        your WooCommerce products and that you have set the product\'s template
        id.</li>
    </ul>
    <p>
    Please <a href="http://www.mystyleplatform.com/contact/" target="_blank" title="contact us">contact us</a> for additional support.
    </p>
    ';

    $sidebar_content = '
    <h5>For more Information:</strong></h5>
    <a href="http://www.mystyleplatform.com/mystyle-personalization-plugin-wordpress-woo-commerce/" target="_blank" title="mystyleplatform.com">mystyleplatform.com</a><br/>
    ';

    //overview tab
    $screen->add_help_tab( array(
        'id' => 'mystyle_overview',
        'title' => 'Overview',
        'content' => $overview_content
    ) );
    //installation tab
    $screen->add_help_tab( array(
        'id' => 'mystyle_installation',
        'title' => 'Installation',
        'content' => $installation_content
    ) );
    //use tab
    /*
    $screen->add_help_tab( array(
        'id' => 'mystyle_use',
        'title' => 'Using MyStyle',
        'content' => $use_content
    ));
     */
    //installation tab
    $screen->add_help_tab( array(
        'id' => 'mystyle_troubleshooting',
        'title' => 'Troubleshooting',
        'content' => $troubleshooting_content
    ) );

    $screen->set_help_sidebar( $sidebar_content );

    return $contextual_help;
}
