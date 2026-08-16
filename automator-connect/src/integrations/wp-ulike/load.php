<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once AC_ABSPATH . 'src/integrations/wp-ulike/wp-ulike-integration.php';

require_once AC_ABSPATH . 'src/integrations/wp-ulike/helpers/wp-ulike-helpers.php';

require_once AC_ABSPATH . 'src/integrations/wp-ulike/triggers/wp-ulike-user-likes-post-type.php';
require_once AC_ABSPATH . 'src/integrations/wp-ulike/triggers/wp-ulike-user-unlikes-post-type.php';
require_once AC_ABSPATH . 'src/integrations/wp-ulike/triggers/wp-ulike-user-likes-comment.php';
require_once AC_ABSPATH . 'src/integrations/wp-ulike/triggers/wp-ulike-user-unlikes-comment.php';


if ( ! class_exists( 'Automator_Connect\Integrations\WP_Ulike\WP_Ulike_Integration' ) ) {
	return;
}

// Set main integeration object.
new Automator_Connect\Integrations\WP_Ulike\WP_Ulike_Integration();
