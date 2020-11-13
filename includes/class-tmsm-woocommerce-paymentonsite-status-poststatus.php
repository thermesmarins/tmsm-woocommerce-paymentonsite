<?php

/**
 * Define the processed status
 *
 * @link       https://github.com/thermesmarins/
 * @since      1.0.0
 *
 * @package    Tmsm_Woocommerce_Customeramdin
 * @subpackage Tmsm_Woocommerce_Customadmin/includes
 */

class Tmsm_Woocommerce_Paymentonsite_Status_Poststatus {


	/**
	 * Register post status: processed
	 */
	public function register_post_status_paymentonsite() {
		register_post_status( 'wc-paymentonsite', array(
			'label'                     => __('Payment On Site', 'Order status', 'tmsm-woocommerce-paymentonsite-status'),
			'public'                    => true,
			'show_in_admin_status_list' => true,
			'show_in_admin_all_list'    => true,
			'exclude_from_search'       => false,
			'label_count'               => _n_noop( 'Payment On Site <span class="count">(%s)</span>',
				'Payment On Site <span class="count">(%s)</span>', 'tmsm-woocommerce-paymentonsite-status' ),
		) );
	}


}
