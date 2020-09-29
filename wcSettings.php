<?php


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


if ( ! class_exists( 'WC_Settings_MyPlugin' ) ) :


    function my_plugin_add_settings() {

        /**
         * Settings class
         *
         * @since 1.0.0
         */
        class WC_Settings_MyPlugin extends WC_Settings_Page {


            /**
             * Setup settings class
             *
             * @since  1.0
             */
            public function __construct() {

                $this->id    = 'myplugin';
                $this->label = __( 'PluginName', 'my-textdomain' );

                add_filter( 'woocommerce_settings_tabs_array',        array( $this, 'add_settings_page' ), 20 );
                add_action( 'woocommerce_settings_' . $this->id,      array( $this, 'output' ) );
                add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
                add_action( 'woocommerce_sections_' . $this->id,      array( $this, 'output_sections' ) );

            }


            /**
             * Get sections
             *
             * @return array
             */
            public function get_sections() {

                $sections = array(
                    ''         => __( 'Section 1', 'my-textdomain' ),
                    'second' => __( 'Section 2', 'my-textdomain' )
                );

                return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
            }


            /**
             * Get settings array
             *
             * @since 1.0.0
             * @param string $current_section Optional. Defaults to empty string.
             * @return array Array of settings
             */
            public function get_settings( $current_section = '' ) {

                if ( 'second' == $current_section ) {

                    /**
                     * Filter Plugin Section 2 Settings
                     *
                     * @since 1.0.0
                     * @param array $settings Array of the plugin settings
                     */
                    $settings = apply_filters( 'myplugin_section2_settings', array(

                        array(
                            'name' => __( 'Group 1', 'my-textdomain' ),
                            'type' => 'title',
                            'desc' => '',
                            'id'   => 'myplugin_group1_options',
                        ),

                        array(
                            'type'     => 'checkbox',
                            'id'       => 'myplugin_checkbox_1',
                            'name'     => __( 'Do a thing?', 'my-textdomain' ),
                            'desc'     => __( 'Enable to do something', 'my-textdomain' ),
                            'default'  => 'no',
                        ),

                        array(
                            'type' => 'sectionend',
                            'id'   => 'myplugin_group1_options'
                        ),

                        array(
                            'name' => __( 'Group 2', 'my-textdomain' ),
                            'type' => 'title',
                            'desc' => '',
                            'id'   => 'myplugin_group2_options',
                        ),

                        array(
                            'type'     => 'select',
                            'id'       => 'myplugin_select_1',
                            'name'     => __( 'What should happen?', 'my-textdomain' ),
                            'options'  => array(
                                'something' => __( 'Something', 'my-textdomain' ),
                                'nothing' 	=> __( 'Nothing', 'my-textdomain' ),
                                'idk'    	=> __( 'IDK', 'my-textdomain' ),
                            ),
                            'class'    => 'wc-enhanced-select',
                            'desc_tip' => __( 'Don\'t ask me!', 'my-textdomain' ),
                            'default'  => 'idk',
                        ),

                        array(
                            'type' => 'sectionend',
                            'id'   => 'myplugin_group2_options'
                        ),

                    ) );

                } else {

                    /**
                     * Filter Plugin Section 1 Settings
                     *
                     * @since 1.0.0
                     * @param array $settings Array of the plugin settings
                     */
                    $settings = apply_filters( 'myplugin_section1_settings', array(

                        array(
                            'name' => __( 'Important Stuff', 'my-textdomain' ),
                            'type' => 'title',
                            'desc' => '',
                            'id'   => 'myplugin_important_options',
                        ),

                        array(
                            'type'     => 'select',
                            'id'       => 'myplugin_select_1',
                            'name'     => __( 'Choose your favorite', 'my-textdomain' ),
                            'options'  => array(
                                'vanilla'        => __( 'Vanilla', 'my-textdomain' ),
                                'chocolate'		 => __( 'Chocolate', 'my-textdomain' ),
                                'strawberry'     => __( 'Strawberry', 'my-textdomain' ),
                            ),
                            'class'    => 'wc-enhanced-select',
                            'desc_tip' => __( 'Be honest!', 'my-textdomain' ),
                            'default'  => 'vanilla',
                        ),

                        array(
                            'type' => 'sectionend',
                            'id'   => 'myplugin_important_options'
                        ),

                    ) );

                }

                /**
                 * Filter MyPlugin Settings
                 *
                 * @since 1.0.0
                 * @param array $settings Array of the plugin settings
                 */
                return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );

            }


            /**
             * Output the settings
             *
             * @since 1.0
             */
            public function output() {

                global $current_section;

                $settings = $this->get_settings( $current_section );
                WC_Admin_Settings::output_fields( $settings );
            }


            /**
             * Save settings
             *
             * @since 1.0
             */
            public function save() {


                DKP_DKPlus::syncAllProducts();
            }

        }

        return new WC_Settings_MyPlugin();

    }
    add_filter( 'woocommerce_get_settings_pages', 'my_plugin_add_settings', 15 );

endif;