<?php
/**
 * The base class for admin settings pages.
 * @author   Jamsheed KM
 * @since    1.0.0
 *
 * @package    jkm-checkout-captcha-for-woo
 * @subpackage jkm-checkout-captcha-for-woo/admin
 */

if(!defined('WPINC')){ die; }

if(!class_exists('JKMCCFW_Admin_Settings')):

abstract class JKMCCFW_Admin_Settings{
	protected $page_id = '';
	protected $section_id = '';
	
	protected $tabs = '';
	protected $sections = '';

	 public function __construct() {
	 	
    }

}
endif;