<?php

/*
 * Eclipse is a Geeklog theme, not a plugin.
 *
 * Geeklog's plugin uploader reads $pi_name from admin/install.php before it
 * moves plugin admin/public directories.  A sentinel name keeps an accidental
 * Eclipse upload from being treated as a plugin named "eclipse" while this
 * file remains inert when installed under layout/eclipse/.
 */
$pi_name='__eclipse_theme_package__';
