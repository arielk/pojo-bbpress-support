<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Pojo_Settings_Supports extends Pojo_Settings_Page_Base {

	public function section_pages( $sections = array() ) {
		$fields = array();

		$fields[] = array(
			'id' => 'pojo_support_forum_id',
			'title' => __( 'Support Forum', 'pojo-bbpress-support' ),
			'std' => '0',
		);

		$fields[] = array(
			'id' => 'pojo_features_forum_id',
			'title' => __( 'Features Forum', 'pojo-bbpress-support' ),
			'std' => '0',
		);
		
		$sections[] = array(
			'id' => 'section-main-site',
			'page' => 'bbpress',
			'title' => __( 'Support Forums', 'pojo-bbpress-support' ),
			'intro' => __( 'Setup your forums IDs', 'pojo-bbpress-support' ),
			'fields' => $fields,
		);

		return $sections;
	}
	
	public function __construct( $priority = 10 ) {
		$this->_page_id         = 'pojo-supports';
		$this->_page_title      = __( 'Supports Settings', 'pojo-bbpress-support' );
		$this->_page_menu_title = __( 'Supports', 'pojo-bbpress-support' );
		$this->_page_type       = 'submenu';
		$this->_page_parent     = 'options-general.php';

		add_filter( 'pojo_register_settings_sections', array( &$this, 'section_pages' ), 100 );

		//parent::__construct( $priority );
	}

}