<?php

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'marketcampus');

/** Database username */
define('DB_USER', 'root');

/** Database password */
define('DB_PASSWORD', '');

/** Database hostname */
define('DB_HOST', 'localhost');

/** Database charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The database collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'gu96h4rLX9ddwS$.`Gk9&J){59g|47CUSYtVun$GEP0p<#P8kAFDH2 un~dp_~.W');
define('SECURE_AUTH_KEY',  '#.)ciEq!m+OER25:w,&b.JpRp9AHxxf4#%a</(HGCQh=5Id.$x?qodK3CG)/.Xeq');
define('LOGGED_IN_KEY',    '[lzQ4(}z=NaRAX9%2?Mi,oB~qno@CRmH3%%l!L=?,{qMN4e3~2Tf?`D(eH%(&+cv');
define('NONCE_KEY',        ',I1GApEz1 /8N+_y?eR$77{KQY4AIaxKR 4{:{@n@R;WvI+uk}wrG[E$ 8jE*?iZ');
define('AUTH_SALT',        'vL4ElkmU[9?z+n0m_C.A,$<*m&fD@s)&H[fpV IJu<K~|DQ)52Q~Gr#4?E<F[%qu');
define('SECURE_AUTH_SALT', 'omnb+$tb37S{O;5IB]z`Wv$N=*Y5@EX+mP|0NsbV{TubHz@E&djUd=q<yn(&H~Zg');
define('LOGGED_IN_SALT',   ',x7y0zOak*%v4`)eEf{booaowFXEr}L;3FV6@N_tDe6S2j]|=]}d~gf]`/exv${0');
define('NONCE_SALT',       '#,DGvS(C6|/*Y(6j[z`m3EEp(@T&VX!O3q,4ewC4[^&T^<6_?|T)5qCr/l~2/v{C');

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
@ini_set('display_errors', 0);


/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if (! defined('ABSPATH')) {
	define('ABSPATH', __DIR__ . '/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
