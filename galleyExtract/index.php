<?php

/**
 * @defgroup plugins_importexport_jgate
 */
 
/**
 * @file plugins/importexport/galleyExtract/index.php
 *
 * Copyright (c) 2013 Rodrigo De la garza
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_importexport_galleyExtract
 * @brief Wrapper for galleyExtact export plugin.
 *
 */

// $Id$


require_once('GalleyExtractPlugin.inc.php');

return new GalleyExtractPlugin();

?>
