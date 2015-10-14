<?php

/**
 * WordPress settings API demo class
 *
 * @author Tareq Hasan
 */
if ( !class_exists('WeDevs_Settings_API_Test' ) ):
class WeDevs_Settings_API_Test {

    private $settings_api;

    function __construct() {
        $this->settings_api = new WeDevs_Settings_API;

        add_action( 'admin_init', array($this, 'admin_init') );
        add_action( 'admin_menu', array($this, 'admin_menu') );
    }

    function admin_init() {

        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );

		
        //initialize settings
        $this->settings_api->admin_init();
    }

    function admin_menu() {
        add_options_page( 'Xapo Settings', 'Xapo Settings', 'delete_posts', 'settings_api_test', array($this, 'plugin_page') );
    }

    function get_settings_sections() {
        $sections = array(
            array(
                'id' => 'wedevs_basics',
                'title' => __( 'General Settings', 'wedevs' )
            ),
         array(
                'id' => 'wedevs_advanced',
                'title' => __( 'Statistics', 'wedevs' ),
				'callback'=>'xapo_statistics_callback'
            ) /*,
            array(
                'id' => 'wedevs_others',
                'title' => __( 'Other Settings', 'wpuf' )
            )*/
        );
        return $sections;
    }

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    function get_settings_fields() {
        $settings_fields = array(
            'wedevs_basics' => array(
                array(
                    'name'              => 'rewards',
                    'label'             => __( 'Rewards', 'wedevs' ),
                    'desc'              => __( 'Input the rewards and the weight of each possible prize using the format reward*weight sepparated by commas. Units are in Satoshis.
For example: 100*2, 200*1 means that the chances of a user winning 100 satoshis are double than winning 200 Satoshis.', 'wedevs' ),
                    'type'              => 'text',
                    'default'           => '10*8, 20*4, 30*2, 40',
                ),
                array(
                    'name'              => 'referral_percentage',
                    'label'             => __( 'Referral Percentage', 'wedevs' ),
                    'desc'              => __( 'The percentage of the claim that users take by promoting your Faucet', 'wedevs' ),
                    'type'              => 'number',
                    'default'           => '20',
                    'sanitize_callback' => 'intval'
                ),
				 array(
                    'name'              => 'timer',
                    'label'             => __( 'Referral Percentage', 'wedevs' ),
                    'desc'              => __( 'The time interval for your users to redeem', 'wedevs' ),
                    'type'              => 'number',
                    'default'           => '30',
                    'sanitize_callback' => 'intval'
                ),
				 array(
                    'name'              => 'xapo_app_id',
                    'label'             => __( 'Xapo App Key', 'wedevs' ),
                    'desc'              => __( 'App Key provided by Xapo', 'wedevs' ),
                    'type'              => 'text',
                    'default'           => 'Your Xapo App key',
                ),
				 array(
                    'name'              => 'xapo_secret_key',
                    'label'             => __( 'Xapo Secret Key', 'wedevs' ),
                    'desc'              => __( 'Secret Key provided by Xapo', 'wedevs' ),
                    'type'              => 'text',
                    'default'           => 'Your Xapo Secret Key',
                ),
				 array(
                    'name'              => 'solvemedia_challenge_key',
                    'label'             => __( 'Solvemedia Challenge Key', 'wedevs' ),
                    'desc'              => __( 'Solvemedia Challenge Key Goes Here', 'wedevs' ),
                    'type'              => 'text',
                    'default'           => 'Your Challenge Key',
                ),
				 array(
                    'name'              => 'solvemedia_verification_key',
                    'label'             => __( 'Solvemedia Verification Key', 'wedevs' ),
                    'desc'              => __( 'Solvemedia Verification Key Goes Here', 'wedevs' ),
                    'type'              => 'text',
                    'default'           => 'Your Verification Key',
                   
                )
            ),
			
        /*
		    'wedevs_advanced' => array  (
                array(
                    'name'    => 'color',
                    'label'   => __( 'Color', 'wedevs' ),
                    'desc'    => __( 'Color description', 'wedevs' ),
                    'type'    => 'color',
                    'default' => ''
                ),
                array(
                    'name'    => 'password',
                    'label'   => __( 'Password', 'wedevs' ),
                    'desc'    => __( 'Password description', 'wedevs' ),
                    'type'    => 'password',
                    'default' => ''
                ),
                array(
                    'name'    => 'wysiwyg',
                    'label'   => __( 'Advanced Editor', 'wedevs' ),
                    'desc'    => __( 'WP_Editor description', 'wedevs' ),
                    'type'    => 'wysiwyg',
                    'default' => ''
                ),
                array(
                    'name'    => 'multicheck',
                    'label'   => __( 'Multile checkbox', 'wedevs' ),
                    'desc'    => __( 'Multi checkbox description', 'wedevs' ),
                    'type'    => 'multicheck',
                    'default' => array('one' => 'one', 'four' => 'four'),
                    'options' => array(
                        'one'   => 'One',
                        'two'   => 'Two',
                        'three' => 'Three',
                        'four'  => 'Four'
                    )
                ),
                array(
                    'name'    => 'selectbox',
                    'label'   => __( 'A Dropdown', 'wedevs' ),
                    'desc'    => __( 'Dropdown description', 'wedevs' ),
                    'type'    => 'select',
                    'options' => array(
                        'yes' => 'Yes',
                        'no'  => 'No'
                    )
                ),
                array(
                    'name'    => 'password',
                    'label'   => __( 'Password', 'wedevs' ),
                    'desc'    => __( 'Password description', 'wedevs' ),
                    'type'    => 'password',
                    'default' => ''
                ),
                array(
                    'name'    => 'file',
                    'label'   => __( 'File', 'wedevs' ),
                    'desc'    => __( 'File description', 'wedevs' ),
                    'type'    => 'file',
                    'default' => ''
                )
            ),
            'wedevs_others' => array (
                array(
                    'name'    => 'text',
                    'label'   => __( 'Text Input', 'wedevs' ),
                    'desc'    => __( 'Text input description', 'wedevs' ),
                    'type'    => 'text',
                    'default' => 'Title'
                ),
                array(
                    'name'  => 'textarea',
                    'label' => __( 'Textarea Input', 'wedevs' ),
                    'desc'  => __( 'Textarea description', 'wedevs' ),
                    'type'  => 'textarea'
                ),
                array(
                    'name'  => 'checkbox',
                    'label' => __( 'Checkbox', 'wedevs' ),
                    'desc'  => __( 'Checkbox Label', 'wedevs' ),
                    'type'  => 'checkbox'
                ),
                array(
                    'name'    => 'radio',
                    'label'   => __( 'Radio Button', 'wedevs' ),
                    'desc'    => __( 'A radio button', 'wedevs' ),
                    'type'    => 'radio',
                    'options' => array(
                        'yes' => 'Yes',
                        'no'  => 'No'
                    )
                ),
                array(
                    'name'    => 'multicheck',
                    'label'   => __( 'Multile checkbox', 'wedevs' ),
                    'desc'    => __( 'Multi checkbox description', 'wedevs' ),
                    'type'    => 'multicheck',
                    'options' => array(
                        'one'   => 'One',
                        'two'   => 'Two',
                        'three' => 'Three',
                        'four'  => 'Four'
                    )
                ),
                array(
                    'name'    => 'selectbox',
                    'label'   => __( 'A Dropdown', 'wedevs' ),
                    'desc'    => __( 'Dropdown description', 'wedevs' ),
                    'type'    => 'select',
                    'options' => array(
                        'yes' => 'Yes',
                        'no'  => 'No'
                    )
                ),
                array(
                    'name'    => 'password',
                    'label'   => __( 'Password', 'wedevs' ),
                    'desc'    => __( 'Password description', 'wedevs' ),
                    'type'    => 'password',
                    'default' => ''
                ),
                array(
                    'name'    => 'file',
                    'label'   => __( 'File', 'wedevs' ),
                    'desc'    => __( 'File description', 'wedevs' ),
                    'type'    => 'file',
                    'default' => ''
                )
            )
        */
		);

        return $settings_fields;
    }

    function plugin_page() {
        echo '<div class="wrap">';

        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();

        echo '</div>';
    }

    /**
     * Get all the pages
     *
     * @return array page names with key value pairs
     */
    function get_pages() {
        $pages = get_pages();
        $pages_options = array();
        if ( $pages ) {
            foreach ($pages as $page) {
                $pages_options[$page->ID] = $page->post_title;
            }
        }

        return $pages_options;
    }

}
endif;
