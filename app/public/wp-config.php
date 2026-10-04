<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
defined( 'DB_HOST' ) || define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

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
define( 'AUTH_KEY',          '2hwu=K1IP,4HD `4F3 =ja]r:]~}dyg+#4n#^IhxuEUeF$Hp5Vwz [~5 Z^k&Qri' );
define( 'SECURE_AUTH_KEY',   'O-TGGhjA(Jte}hB%pSi=HAK=c)*OgbowF9=FC-a^DEg.>;xYfmJ.!)yC{6Vc@yyg' );
define( 'LOGGED_IN_KEY',     'pT4K[,W/#sgOBFAs*@.Yj%1/IjUIR`EMj~)*hMK/2Au0X+iC^vR|E5-n:|Dy `yN' );
define( 'NONCE_KEY',         'RZe h#ewX8yu{m|`DkmI@RZg@/_/)^U6~DC5W^EJEQ^),+ -<=kleRv,ACN2Ul5q' );
define( 'AUTH_SALT',         'hNgZ4zsTwiD NB1e,Xyb^&P.2dlgBhS&oy}+2_n y//@mdHNUK./-nepf0@2G|H%' );
define( 'SECURE_AUTH_SALT',  'oXVB%oimyW/ 91nGTBk#duhI.GG#~Q)oNH154pE^sfUX*F+HXJ=9A[E/gJTq6D3v' );
define( 'LOGGED_IN_SALT',    '?77o~@;&S1z*7}@,aZ/XMd6G^>y.,+MDHb+Ty{S?lghEF9@H!.gqa&!y,/Ed*4uj' );
define( 'NONCE_SALT',        '`G{d,5NZ$;;TbNZ)LY:pT^FVLFTEMQM}4?~YDf6sHkhFgV[mmRFd;WCG`7SuNEG(' );
define( 'WP_CACHE_KEY_SALT', '=XkX]TSF50nTS$|Qq=0@%$.#|RU2tw~=6FQEf,p?yO;6GY2IWYq-]kb8.5mE:iPx' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
