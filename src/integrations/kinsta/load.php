<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once AC_ABSPATH . 'src/integrations/kinsta/kinsta-integration.php';

require_once AC_ABSPATH . 'src/integrations/kinsta/settings/kinsta-settings.php';

require_once AC_ABSPATH . 'src/integrations/kinsta/helpers/kinsta-api-caller.php';
require_once AC_ABSPATH . 'src/integrations/kinsta/helpers/kinsta-api-client.php';
require_once AC_ABSPATH . 'src/integrations/kinsta/helpers/kinsta-api-credentials.php';
require_once AC_ABSPATH . 'src/integrations/kinsta/helpers/kinsta-app-helpers.php';

require_once AC_ABSPATH . 'src/integrations/kinsta/actions/kinsta-create-plain-site.php';
require_once AC_ABSPATH . 'src/integrations/kinsta/actions/kinsta-delete-site.php';
require_once AC_ABSPATH . 'src/integrations/kinsta/actions/kinsta-get-operation-status.php';

if ( ! class_exists( 'Automator_Connect\Integrations\Kinsta\Kinsta_Integration' ) ) {
	return;
}

// Set main integeration object.
new Automator_Connect\Integrations\Kinsta\Kinsta_Integration();
