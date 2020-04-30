<?php
/**
 * La configuration de base de votre installation WordPress.
 *
 * Ce fichier contient les réglages de configuration suivants : réglages MySQL,
 * préfixe de table, clés secrètes, langue utilisée, et ABSPATH.
 * Vous pouvez en savoir plus à leur sujet en allant sur
 * {@link http://codex.wordpress.org/fr:Modifier_wp-config.php Modifier
 * wp-config.php}. C’est votre hébergeur qui doit vous donner vos
 * codes MySQL.
 *
 * Ce fichier est utilisé par le script de création de wp-config.php pendant
 * le processus d’installation. Vous n’avez pas à utiliser le site web, vous
 * pouvez simplement renommer ce fichier en "wp-config.php" et remplir les
 * valeurs.
 *
 * @package WordPress
 */

// ** Réglages MySQL - Votre hébergeur doit vous fournir ces informations. ** //
/** Nom de la base de données de WordPress. */
define( 'DB_NAME', 'sididb' );

/** Utilisateur de la base de données MySQL. */
define( 'DB_USER', 'root' );

/** Mot de passe de la base de données MySQL. */
define( 'DB_PASSWORD', '' );

/** Adresse de l’hébergement MySQL. */
define( 'DB_HOST', 'localhost' );

/** Jeu de caractères à utiliser par la base de données lors de la création des tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** Type de collation de la base de données.
  * N’y touchez que si vous savez ce que vous faites.
  */
define('DB_COLLATE', '');

/**#@+
 * Clés uniques d’authentification et salage.
 *
 * Remplacez les valeurs par défaut par des phrases uniques !
 * Vous pouvez générer des phrases aléatoires en utilisant
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ le service de clefs secrètes de WordPress.org}.
 * Vous pouvez modifier ces phrases à n’importe quel moment, afin d’invalider tous les cookies existants.
 * Cela forcera également tous les utilisateurs à se reconnecter.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'kV~?:,n#P}r<E0Gwnp8xD3yL[L,d`fc4&V^ E6fxIE}}d=S))R[dH)^LU[2ZaU0)' );
define( 'SECURE_AUTH_KEY',  '_ZRGlq,yDRn[&),F[A;2?Z.Zh$b?Jwl$i( ~L:d6,;V/A@%}Gqf/5(HKF*hh}(DY' );
define( 'LOGGED_IN_KEY',    '` .tL,i-IdD7QMXood$7?*;>vo>Wh?t2s4|cKoL_zrU$cCx$C*nqQj(~y5R]filT' );
define( 'NONCE_KEY',        'WL-).SIWf2_evIk4-]/:Wh@hCjN80S5MERms+1Ea?|IOp(!or-0EVXRSe{ZEG `p' );
define( 'AUTH_SALT',        'j3W0]@TLbg+`gsJbyirZ`o>{_q/aPd[%Nuj,6.Ft.N:q}Rhw` j @;#TU/r%^LXa' );
define( 'SECURE_AUTH_SALT', 'nrrH,;@A*1X^,3 %<[Lfd<;6Ymxn`aks[P.hOl75):H1yRwzJg8;Rl1>><R)6iIZ' );
define( 'LOGGED_IN_SALT',   'u]s?_X=<H6OI]oGRXNc`~[tSUixt lPi2sZGaTm^2;HVvK8Zlp]V2)|PrvS?5 nF' );
define( 'NONCE_SALT',       '5vON.F mMO19-U.kul!z]OyyySkh4=X:Vf[4`<(=^:U*`R0UUhd~rE&ax1#x`TTL' );
/**#@-*/

/**
 * Préfixe de base de données pour les tables de WordPress.
 *
 * Vous pouvez installer plusieurs WordPress sur une seule base de données
 * si vous leur donnez chacune un préfixe unique.
 * N’utilisez que des chiffres, des lettres non-accentuées, et des caractères soulignés !
 */
$table_prefix = 'wp_';

/**
 * Pour les développeurs : le mode déboguage de WordPress.
 *
 * En passant la valeur suivante à "true", vous activez l’affichage des
 * notifications d’erreurs pendant vos essais.
 * Il est fortemment recommandé que les développeurs d’extensions et
 * de thèmes se servent de WP_DEBUG dans leur environnement de
 * développement.
 *
 * Pour plus d’information sur les autres constantes qui peuvent être utilisées
 * pour le déboguage, rendez-vous sur le Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* C’est tout, ne touchez pas à ce qui suit ! Bonne publication. */

/** Chemin absolu vers le dossier de WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Réglage des variables de WordPress et de ses fichiers inclus. */
require_once(ABSPATH . 'wp-settings.php');
