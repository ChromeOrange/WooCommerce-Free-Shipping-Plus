<?php
/*
Plugin Name: WooCommerce Free Shipping Plus
Plugin URI: http://codeeveryday.co/woocommerce-free-shipping-plus/
Description: Improves on WooCommerce Free Shipping.
Version: 0.1.0
Author: Andrew Benbow
Author URI: http://codeeveryday.co/

Text Domain: wc-freeshipping-plus
Domain Path: /languages/

*/

/*
    Copyright 2012  Andrew Benbow  (email : andrew@chromeorange.co.uk) 

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( is_woocommerce_active() ) {

    /**
     * Localisation
     */
    load_plugin_textdomain( 'wc-freeshipping-plus', false, dirname( plugin_basename( __FILE__ ) ) . '/' );

    /**
     * woocommerce_product_gallery_slider class
     */
    if ( ! class_exists( 'WC_Free_Shipping_Plus' ) ) {

        class WC_Free_Shipping_Plus {

            public function __construct() {

                add_action( 'woocommerce_product_options_shipping', array( $this, 'write_panel' ), 99 );
                add_action( 'woocommerce_process_product_meta', array( $this, 'write_panel_save' ) );

                add_filter( 'woocommerce_package_rates', array( $this, 'unset_free_shipping_method' ) , 10, 2 );

            }


            /**
             * Create new fields in Shipping section of Product 
             *
             * Add them at the bottom.
             */
            function write_panel() {

                echo '<div class="options_group">';

                woocommerce_wp_select( array(   'id'            => '_woocommerce_product_exclude_from_free_shipping', 
                                                'label'         => __('Exclude this product from the WooCommerce Free Shipping method', 'wc-freeshipping-plus'), 
                                                'desc_tip'      => true,
                                                'description'   => __('This option will remove Free Shipping from the available shipping methods if this product is in the cart. Only works with the WooCommerce Free Shipping method.', 'wc_product_gallery_slider'), 
                                                'options'       => array(
                                                                    'no'   => __( 'No', 'wc-freeshipping-plus' ),
                                                                    'yes'  => __( 'Yes', 'wc-freeshipping-plus' )
                                                                ) 
                                            ) 
                                        );

                woocommerce_wp_text_input( array(
                        'id'                => '_woocommerce_product_exclude_from_free_shipping_states',
                        'label'             => __( 'Exclude Free Shipping in these US States', 'wc-freeshipping-plus' ),
                        'desc_tip'          => true,
                        'description'       => __( 'If you want to exclude this product from the WooCommerce Free Shipping method but only in certain US states then tick the option above and enter the US State codes. eg for Hawaii add HI, for Hawaii and Alaska add HI,AK', 'wc-freeshipping-plus' ),
                        'type'              => 'text'
                    ) );

                echo '</div>';

            } // write_panel

            /**
             * Save shipping
             * @param  [type] $post_id [description]
             * @return [type]          [description]
             */
            function write_panel_save( $post_id ) {

                $woocommerce_product_exclude_from_free_shipping           = esc_attr( $_POST['_woocommerce_product_exclude_from_free_shipping'] );
                $woocommerce_product_exclude_from_free_shipping_states    = esc_attr( $_POST['_woocommerce_product_exclude_from_free_shipping_states'] );
                update_post_meta( $post_id, '_woocommerce_product_exclude_from_free_shipping', $woocommerce_product_exclude_from_free_shipping );
                update_post_meta( $post_id, '_woocommerce_product_exclude_from_free_shipping_states', $woocommerce_product_exclude_from_free_shipping_states );

            } // write_panel_save


            /**
             * Check the cart for specific products, remove Free Shipping method if they are present
             */

            function unset_free_shipping_method( $rates, $package ) {
 
                /**
                 * loop through the cart checking for _woocommerce_product_exclude_from_free_shipping meta field
                 */ 
                foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {

                    if( get_post_meta( $values['product_id'], '_woocommerce_product_exclude_from_free_shipping', TRUE ) == 'yes' ) {

                        $woocommerce_product_exclude_from_free_shipping_states = get_post_meta( $values['product_id'], '_woocommerce_product_exclude_from_free_shipping_states', TRUE );

                        if ( isset($woocommerce_product_exclude_from_free_shipping_states) && $woocommerce_product_exclude_from_free_shipping_states != '' ) {

                            $states_array = explode( ',', $woocommerce_product_exclude_from_free_shipping_states );

                            if ( in_array( WC()->customer->get_shipping_state(), $states_array ) ) {

                                /**
                                 * Unset the shipping methods here
                                 */
                                unset( $rates['free_shipping'] );
 
                                // The rates have been removed, no point in carrying on
                                break;
                            }

                        } else {
                                
                            /**
                             * Unset the shipping methods here
                             */
                            unset( $rates['free_shipping'] );
 
                            // The rates have been removed, no point in carrying on
                            break;                            
                        }
        
                    }
 
                }
 
                // Return what's left of the $rates array
                return $rates;
 
            } // unset_free_shipping_method

        } // WC_Free_Shipping_Plus

        $WC_Free_Shipping_Plus = new WC_Free_Shipping_Plus();

    } // if ( ! class_exists( 'WC_Free_Shipping_Plus' ) )

} // is_woocommerce_active



