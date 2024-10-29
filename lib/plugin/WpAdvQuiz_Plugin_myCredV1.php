<?php
/**
 * WP-Adv-Quiz Hook
 * @version 1.0
 */
add_filter( 'mycred_setup_hooks', 'register_wp_adv_quiz_hook_in_mycred' );
function register_wp_adv_quiz_hook_in_mycred( $installed ) {

	$installed['wpadvquiz'] = array(
		'title'       => __( 'WP-Adv-Quiz', 'textdomain' ),
		'description' => __( 'This hook award / deducts points for users completing quizzes through the WP-Adv-Quiz plugin.', 'textdomain' ),
		'callback'    => array( 'myCRED_Hook_WP_Adv_Quiz' )
	);
	return $installed;

}

/**
 * WP-Adv-Quiz Badges
 * Add support for creating badges for this hook.
 * @version 1.0
 */
add_filter( 'mycred_all_references', 'register_wp_adv_quiz_mycred_badge' );
function register_wp_adv_quiz_mycred_badge( $references ) {

	$references['completing_quiz']      = __( 'Completing Quiz (WP Adv Quiz)', 'textdomain' );
	$references['completing_quiz_full'] = __( 'Completing Quiz Full (WP Adv Quiz)', 'textdomain' );

	return $references;

}

/**
 * WP-Adv-Quiz Hook Class
 * @version 1.0.1
 */
add_action( 'mycred_pre_init', 'load_wp_adv_quiz_mycred_hook' );
function load_wp_adv_quiz_mycred_hook() {
	class myCRED_Hook_WP_Adv_Quiz extends myCRED_Hook {

		/**
		 * Construct Hook
		 */
		function __construct( $hook_prefs, $type = 'mycred_default' ) {

			// We use the abstract classes constructor to construct our own
			// We need to provide a unique hook id and the default settings
			parent::__construct( array(
				'id'       => 'wpadvquiz',
				'defaults' => array(
					'completed'    => array(
						'creds'  => 1,
						'log'    => '%plural% for completing quiz',
						'limit'  => 0
					),
					'fullscrore'    => array(
						'creds'  => 1,
						'log'    => '%plural% for 100% quiz completion',
						'limit'  => 0
					)
				)
			), $hook_prefs, $type );

		}

		/**
		 * Hook into WP Adv Quiz
		 * The run() method fires of during WordPress's init instance.
		 * This method must be set and should be used to "hook" into the third-party
		 * plugin that we want to support.
		 * @since 1.0
		 * @version 1.0
		 */
		function run() {

			// Zero points means this feature is "off".
			if ( $this->prefs['completed']['creds'] != 0 )
				add_action( 'wp_adv_quiz_completed_quiz',             array( $this, 'completed_quiz' ) );

			// Zero points means this feature is "off".
			if ( $this->prefs['fullscrore']['creds'] != 0 )
				add_action( 'wp_adv_quiz_completed_quiz_100_percent', array( $this, 'completed_quiz_full' ) );

		}

		/**
		 * Complete Quiz
		 * This instance is provided by WP Adv Quiz and fires of when a 
		 * quiz was successfully completed.
		 * @since 1.0
		 * @version 1.2
		 */
		function completed_quiz() {

			// Must be logged in
			if ( ! is_user_logged_in() ) return;

			// We need a user ID and a Quiz ID
			$user_id = get_current_user_id();
			$quiz_id = absint( $_REQUEST['quizId'] );

			// Check for exclusions
			if ( $this->core->exclude_user( $user_id ) ) return;

			// Award if not over limit
			if ( ! $this->over_hook_limit( 'completed', 'completing_quiz', $user_id ) )
				$this->core->add_creds(
					'completing_quiz',
					$user_id,
					$this->prefs['completed']['creds'],
					$this->prefs['completed']['log'],
					$quiz_id,
					array( 'ref_type' => 'post' ),
					$this->mycred_type
				);

		}
		
		/**
		 * Complete Quiz Full
		 * This instance is provided by WP Adv Quiz and fires of when a 
		 * quiz was successfully completed with full marks.
		 * @since 1.0
		 * @version 1.2
		 */
		function completed_quiz_full() {

			// Must be logged in
			if ( ! is_user_logged_in() ) return;

			// We need a user ID and a Quiz ID
			$user_id = get_current_user_id();
			$quiz_id = absint( $_REQUEST['quizId'] );

			// Check for exclusions
			if ( $this->core->exclude_user( $user_id ) ) return;

			// Award if not over limit
			if ( ! $this->over_hook_limit( 'fullscrore', 'completing_quiz_full', $user_id ) )
				$this->core->add_creds(
					'completing_quiz_full',
					$user_id,
					$this->prefs['fullscrore']['creds'],
					$this->prefs['fullscrore']['log'],
					$quiz_id,
					array( 'ref_type' => 'post' ),
					$this->mycred_type
				);

		}

		/**
		 * Preference for this Hook
		 * The preferences() methos is optional and only needs to be defined
		 * if this hook needs to have settings that a user must have access to.
		 * To ensure the settings are correctly saved, you should use the built-in
		 * $this->field_name() and $this->field_id() methods. They will do the grunt
		 * work for you.
		 * @since 1.0
		 * @version 1.2.1
		 */
		public function preferences() {

			$prefs = $this->prefs;

?>
<label class="subheader" for="<?php echo $this->field_id( array( 'completed' => 'creds' ) ); ?>"><?php _e( 'Completing Quiz', 'textdomain' ); ?></label>
<ol>
	<li>
		<div class="h2"><input type="text" name="<?php echo $this->field_name( array( 'completed' => 'creds' ) ); ?>" id="<?php echo $this->field_id( array( 'completed' => 'creds' ) ); ?>" value="<?php echo esc_attr( $prefs['completed']['creds'] ); ?>" size="8" /></div>
	</li>
	<li class="empty">&nbsp;</li>
	<li>
		<label for="<?php echo $this->field_id( array( 'completed' => 'log' ) ); ?>"><?php _e( 'Log template', 'textdomain' ); ?></label>
		<div class="h2"><input type="text" name="<?php echo $this->field_name( array( 'completed' => 'log' ) ); ?>" id="<?php echo $this->field_id( array( 'completed' => 'log' ) ); ?>" value="<?php echo esc_attr( $prefs['completed']['log'] ); ?>" class="long" /></div>
		<span class="description"><?php echo $this->core->available_template_tags( array( 'general', 'post' ) ); ?></span>
	</li>
	<li class="empty">&nbsp;</li>
	<li>
		<?php echo $this->hook_limit_setting( $this->field_name( array( 'completed' => 'limit' ) ), $this->field_id( array( 'completed' => 'limit' ) ), $prefs['completed']['limit'] ); ?>
	</li>
</ol>
<label class="subheader" for="<?php echo $this->field_id( array( 'fullscrore' => 'creds' ) ); ?>"><?php _e( '100% Completion', 'textdomain' ); ?></label>
<ol>
	<li>
		<div class="h2"><input type="text" name="<?php echo $this->field_name( array( 'fullscrore' => 'creds' ) ); ?>" id="<?php echo $this->field_id( array( 'fullscrore' => 'creds' ) ); ?>" value="<?php echo esc_attr( $prefs['fullscrore']['creds'] ); ?>" size="8" /></div>
	</li>
	<li class="empty">&nbsp;</li>
	<li>
		<label for="<?php echo $this->field_id( array( 'fullscrore' => 'log' ) ); ?>"><?php _e( 'Log template', 'textdomain' ); ?></label>
		<div class="h2"><input type="text" name="<?php echo $this->field_name( array( 'fullscrore' => 'log' ) ); ?>" id="<?php echo $this->field_id( array( 'fullscrore' => 'log' ) ); ?>" value="<?php echo esc_attr( $prefs['fullscrore']['log'] ); ?>" class="long" /></div>
		<span class="description"><?php echo $this->core->available_template_tags( array( 'general', 'post' ) ); ?></span>
	</li>
	<li class="empty">&nbsp;</li>
	<li>
		<?php echo $this->hook_limit_setting( $this->field_name( array( 'fullscrore' => 'limit' ) ), $this->field_id( array( 'fullscrore' => 'limit' ) ), $prefs['fullscrore']['limit'] ); ?>
	</li>
</ol>
<?php

		}

		/**
		 * Sanitise Preferences
		 * The sanitise_preferences() method fires when a user saved the hook settings.
		 * It should be used to sanitize and validate settings entered by the user and
		 * if the hook supports "Hook limits", save the limits setup.
		 * @since 1.2
		 * @version 1.0
		 */
		function sanitise_preferences( $data ) {

			// Hook limits consists of two variables: The actual limit and the frequency.
			// These two settings needs to be combined into one string divided by a forward slash.
			if ( isset( $data['completed']['limit'] ) && isset( $data['completed']['limit_by'] ) ) {
				$limit = sanitize_text_field( $data['completed']['limit'] );
				if ( $limit == '' ) $limit = 0;
				$data['completed']['limit'] = $limit . '/' . $data['completed']['limit_by'];
				unset( $data['completed']['limit_by'] );
			}

			// Hook limits consists of two variables: The actual limit and the frequency.
			// These two settings needs to be combined into one string divided by a forward slash.
			if ( isset( $data['fullscrore']['limit'] ) && isset( $data['fullscrore']['limit_by'] ) ) {
				$limit = sanitize_text_field( $data['fullscrore']['limit'] );
				if ( $limit == '' ) $limit = 0;
				$data['fullscrore']['limit'] = $limit . '/' . $data['fullscrore']['limit_by'];
				unset( $data['fullscrore']['limit_by'] );
			}

			return $data;

		}

	}
}

?>