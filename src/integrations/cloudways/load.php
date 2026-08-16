<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once AC_ABSPATH . 'src/integrations/cloudways/cloudways-integration.php';

require_once AC_ABSPATH . 'src/integrations/cloudways/settings/cloudways-settings.php';

require_once AC_ABSPATH . 'src/integrations/cloudways/helpers/cloudways-api-caller.php';
require_once AC_ABSPATH . 'src/integrations/cloudways/helpers/cloudways-api-client.php';
require_once AC_ABSPATH . 'src/integrations/cloudways/helpers/cloudways-api-credentials.php';
require_once AC_ABSPATH . 'src/integrations/cloudways/helpers/cloudways-app-helpers.php';

require_once AC_ABSPATH . 'src/integrations/cloudways/actions/cloudways-add-app.php';
require_once AC_ABSPATH . 'src/integrations/cloudways/actions/cloudways-remove-app.php';
require_once AC_ABSPATH . 'src/integrations/cloudways/actions/cloudways-create-app-backup.php';
require_once AC_ABSPATH . 'src/integrations/cloudways/actions/cloudways-get-operation-status.php';

if ( ! class_exists( 'Automator_Connect\Integrations\Cloudways\Cloudways_Integration' ) ) {
	return;
}

// Set main integeration object.
new Automator_Connect\Integrations\Cloudways\Cloudways_Integration();
