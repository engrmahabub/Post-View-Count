<?php 
/*
Plugin Name: SM Post View Count
Author: Mahabubur Rahman
*/

/**
* 
*/
class SMPostViewCount
{
	
	function __construct()
	{
		register_activation_hook(__FILE__,array($this,'sm_database_install'));
		add_filter ('the_content', array($this,'sm_post_view_count'), 100);
	}

	public function sm_database_install()
	{
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$spvc_total=$wpdb->prefix.'spvc_total';
		$sql = "CREATE TABLE IF NOT EXISTS $spvc_total (
		  id int(11) NOT NULL AUTO_INCREMENT,
		  post_id int(11) DEFAULT NULL,
		  count int(11) DEFAULT NULL,
		  update_at datetime DEFAULT NULL,
		  create_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
		  PRIMARY KEY id (id)
		) $charset_collate;";

		$wpdb->query($sql);

		$spvc_day=$wpdb->prefix.'spvc_details';
		$sql = "CREATE TABLE IF NOT EXISTS $spvc_day (
		  id int(11) NOT NULL AUTO_INCREMENT,
		  post_id int(11) DEFAULT NULL,
		  view_date date DEFAULT NULL,
		  count int(11) DEFAULT NULL,
		  create_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  update_at datetime DEFAULT NULL,
		  PRIMARY KEY id (id)
		) $charset_collate;";

		$wpdb->query($sql);

		// require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		// dbDelta( $sql );
	}
	public function sm_post_view_count(){
		print get_the_content();
		if(!is_page() && is_single()):
		global $wp;
		$current_url = home_url(add_query_arg(array(),$wp->request));
		global $wpdb;
		$spvc_total 		= $wpdb->get_var( "SELECT COUNT(*) FROM ".$wpdb->prefix."spvc_total WHERE post_id=".get_the_ID());
		$spvc_details 		= $wpdb->get_var( "SELECT COUNT(*) FROM ".$wpdb->prefix."spvc_details WHERE view_date = '".date('Y-m-d')."' AND post_id=".get_the_ID());
		if($spvc_total){
			$spvc_total_view	= $wpdb->get_var( "SELECT count FROM ".$wpdb->prefix."spvc_total WHERE post_id=".get_the_ID());
			$wpdb->update( 
				$wpdb->prefix."spvc_total", 
				array( 
					'post_id' => get_the_ID(),	// string
					'count' => $spvc_total_view+1	// integer (number) 
				), 
				array( 'post_id' => get_the_ID() ), 
				array( 
					'%d',	// value1
					'%d'	// value2
				), 
				array( '%d' ) 
			);
		}else{
			$wpdb->insert($wpdb->prefix."spvc_total",
				array( 'post_id' => get_the_ID(),	'count' => 1), 
				array('%d', '%d' )
			);
		}

		if($spvc_details ){
			$spvc_today_view	= $wpdb->get_var( "SELECT count FROM ".$wpdb->prefix."spvc_details WHERE view_date='".date('Y-m-d')."' AND post_id=".get_the_ID());
			$wpdb->update( 
				$wpdb->prefix."spvc_details", 
				array( 
					'post_id' => get_the_ID(),	// string
					'count' => $spvc_today_view+1	// integer (number) 
				), 
				array( 'post_id' => get_the_ID() ,'view_date' => date('Y-m-d')), 
				array( 
					'%d',	// value1
					'%d'	// value2
				), 
				array( '%d','%s' ) 
			);
		}else{
			$wpdb->insert($wpdb->prefix."spvc_details",
				array( 'post_id' => get_the_ID(),	'count' => 1,	'view_date' => date('Y-m-d')), 
				array('%d', '%d', '%s' )
			);
		}

		$spvc_total_view	= $wpdb->get_var( "SELECT count FROM ".$wpdb->prefix."spvc_total WHERE post_id=".get_the_ID());
		$spvc_today_view	= $wpdb->get_var( "SELECT count FROM ".$wpdb->prefix."spvc_details WHERE view_date='".date('Y-m-d')."' AND post_id=".get_the_ID());
		ob_start();
		?>	

		<div style="text-align: right;">
			<section>Total Views <?php echo $spvc_total_view;?>, Views Today <?php echo $spvc_today_view;?></section>
		</div>
		<?php
		return ob_get_clean();
		endif;
	}
}

global $smpostviewcount;
$smpostviewcount = new SMPostViewCount();