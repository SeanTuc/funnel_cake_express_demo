<?php
/**
 * Settings Tab
 *
 * @author  Corrado Porzio <corradoporzio@gmail.com>
 * @package YITH\ProductAddOns
 * @version 2.0.0
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.

$help = array(

	'help' => array(

		'help-options'     => array(
			'title' => __( 'Help', 'yith-woocommerce-product-add-ons' ),
			'type'  => 'title',
			'desc'  => '',
			'id'    => 'yith-wapo-help-options',
		),
		'help-options-end' => array(
			'id'   => 'yith-wapo-help-options',
			'type' => 'sectionend',
		),
	),
);

return apply_filters( 'yith_wapo_panel_help_options', $help );
