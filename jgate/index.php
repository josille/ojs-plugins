<?php

/**
 * @defgroup plugins_importexport_jgate
 */
 
/**
 * @file plugins/importexport/jgate/index.php
 *
 * Copyright (c) 2013 Rodrigo De la garza
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_importexport_jgate
 * @brief Wrapper for JGate export plugin.
 *
 */

// $Id$


require_once('JGateExportPlugin.inc.php');

return new JGateExportPlugin();

?>
