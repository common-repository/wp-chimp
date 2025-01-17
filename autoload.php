<?php
/**
 * WP_Chimp Autoloader.
 *
 * @package WP_Chimp
 * @since 0.1.0
 */

namespace WP_Chimp;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

/**
 * Autoloader for Classes within the WP_Chimp namespace.
 *
 * @param string $class_name The loaded class name e.g. WP_Chimp\Class_Name.
 */
function autoloader( $class_name ) {

	// If the specified $class_name does not include our namespace, duck out.
	if ( false === strpos( $class_name, 'WP_Chimp' ) || false !== strpos( $class_name, 'WP_Chimp_Tests' ) ) {
		return;
	}

	// Split the class name into an array to read the namespace and class.
	$file_parts = explode( '\\', $class_name );

	// Do a reverse loop through $file_parts to build the path to the file.
	$namespace = '';
	$file_name = '';
	for ( $i = count( $file_parts ) - 1; $i > 0; $i-- ) {

		if ( 'Deps' === $file_parts[1] ) {
			unset( $file_parts[0] );

			$deps_file = implode( '/', $file_parts ) . '.php';
			$file_name = 'packages/' . $deps_file;
			continue;
		}

		// Read the current component of the file part.
		$current = strtolower( $file_parts[ $i ] );
		$current = str_ireplace( '_', '-', $current );

		// If we're at the first entry, then we're at the filename.
		if ( count( $file_parts ) - 1 === $i ) {

			/**
			 * If 'interface' or 'traite is contained in the parts of the file name,
			 * define the $file_name differently so that it's properly loaded.
			 * Otherwise, just set the $file_name equal to that of the class
			 * filename structure.
			 */
			if ( false !== strpos( $current, 'interface' ) || false !== strpos( $current, 'trait' ) ) {

				$current = explode( '-', $current );
				end( $current );

				$str_index = key( $current );
				$keyword = $current[ $str_index ];

				if ( in_array( $keyword, [ 'interface', 'trait' ], true ) ) {
					unset( $current[ $str_index ] );

					$current = implode( '-', $current );
					$file_name = "$keyword-$current.php";
				}
			} else {
				$file_name = "class-$current.php";
			}
		} else {
			$namespace = '/' . $current . $namespace;
		}
	}

	if ( ! $file_name || empty( $file_name ) ) {
		return;
	}

	/**
	 * Now build a path to the file using mapping to the file location.
	 * Also, do not append the /src if it loads from the \Tests namespace.
	 */
	$filepath  = trailingslashit( dirname( __FILE__ ) . '/src' . $namespace );
	$filepath .= $file_name;

	// If the file exists in the specified path, then include it.
	if ( file_exists( $filepath ) ) {
		include_once $filepath;
	} else {

		// Translators: the file path of the class to load.
		$message = __( 'The file attempting to be loaded at %s does not exist.', 'wp-chimp' );
		wp_die(
			wp_kses( sprintf( $message, "<code>${filepath}</code>" ), [ 'code' => true ] )
		);
	}
}

spl_autoload_register( __NAMESPACE__ . '\\autoloader' );
