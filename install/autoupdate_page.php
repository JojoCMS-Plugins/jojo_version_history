<?php
/**
 *                    Jojo CMS
 *                ================
 *
 * Copyright 2009 Jojo CMS
 *
 * See the enclosed file license.txt for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Michael Cochrane <mikec@jojocms.org>
 * @license http://www.fsf.org/copyleft/lgpl.html GNU Lesser General Public License
 * @link    http://www.jojocms.org JojoCMS
 * @package Jojo_VersionHistory
 */

/* History Tab */

// Last Updated Field
$default_fd['page']['pg_updated'] = array(
        'fd_name' => "Last Updated",
        'fd_type' => "timestamp",
        'fd_default' => "CURRENT_TIMESTAMP",
        'fd_order' => "1",
        'fd_tabname' => "History",
    );

// Revision Field
$default_fd['page']['pg_version'] = array(
        'fd_name' => "Revision",
        'fd_type' => "version",
        'fd_default' => "0",
        'fd_help' => "The revision number of this page, indicates the number of times this record has been changed since it was created.",
        'fd_order' => "2",
        'fd_tabname' => "History",
        'fd_showlabel' => "no",
    );
