<?php 
// dynamically creating notification for announcement
add_filter('acf/load_field/key=field_5ccd7e9bf9b65', 'announcement');
function announcement($field) {
	global $post;
	global $wpdb;
	if ( is_admin() ) {
		if (isset($post->post_type, $post->ID) && $post->post_type == 'announcements') {

			/*$current_user = wp_get_current_user();
			$user_id = $current_user->ID;*/
						
			$post_id = $post->ID;
			$title = get_the_title($post_id);
			$content = get_post_field('post_content', $post_id);
			$announcement_subject = get_post_meta( $post_id, 'announcement_subject', true );
			$subject_text = ($announcement_subject == '' || $announcement_subject == 'None') ? '' : $announcement_subject.' - ';
			//var_dump($subject_text);
			$upload = get_field('upload_options', $post_id);
			//$class = get_field('class', $post_id);
			$terms = get_the_terms($post_id, 'classes');
			$classes = array();
			foreach ( $terms as $term ) {
				$classes_ids[] = $term->term_id;
				$classes[] = $term->name;
			}
			$class_ids = join( ", ", $classes_ids );
			$class = join( ", ", $classes );

			$email_results = $wpdb->get_results( "SELECT * FROM parent_email WHERE user_id IN (".$class_ids.")" );
			if ( !empty($email_results) ) {
				$emails = array();
				foreach( $email_results as $email ) {
					$emails[] = $email->user_email;
				}
			}
			$emails = implode(', ', $emails);

			ob_start();
			include(TEMPLATEPATH."/announcement-template.php");
			$MessageTemplate = ob_get_contents();
			ob_end_clean();

			if(isset( $_REQUEST['message'])) {
				$to = 'info@example.com';
				$subject = $subject_text.'Check Our New Announcement';
				$body = $MessageTemplate;
				//$headers[] = array('Content-Type: text/html; charset=UTF-8');
				$headers[] = 'From: Lorem ipsum <noreply@loremipsum.com>';
				$headers[] = 'Bcc: '.$emails;
				wp_mail( $to, $subject, $body, $headers );
				remove_filter( 'wp_mail_content_type', 'wpdocs_set_html_mail_content_type' );
			}

			mv_optin_mail();
			$field['message'] = "
				<p><strong>Please upload using the below options.</strong></p>
				<div style='display:none;'><input type='text' name='message' value='Message' readonly='readonly' /></div>
			";
		}
	}

	return $field;
}

function wpdocs_set_html_mail_content_type() {
	return 'text/html';
}

?>
