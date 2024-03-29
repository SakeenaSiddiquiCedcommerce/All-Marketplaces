<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitc70d3ae47e9b00f342b79455c7b671d9 {

	public static $prefixLengthsPsr4 = array(
		'O' =>
		array(
			'Olifolkerd\\Convertor\\' => 21,
		),
	);

	public static $prefixDirsPsr4 = array(
		'Olifolkerd\\Convertor\\' =>
		array(
			0 => __DIR__ . '/..' . '/olifolkerd/convertor/src',
		),
	);

	public static $classMap = array(
		'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
		'WP_Async_Request'            => __DIR__ . '/..' . '/deliciousbrains/wp-background-processing/classes/wp-async-request.php',
		'WP_Background_Process'       => __DIR__ . '/..' . '/deliciousbrains/wp-background-processing/classes/wp-background-process.php',
	);

	public static function getInitializer( ClassLoader $loader ) {
		return \Closure::bind(
			function () use ( $loader ) {
				$loader->prefixLengthsPsr4 = ComposerStaticInitc70d3ae47e9b00f342b79455c7b671d9::$prefixLengthsPsr4;
				$loader->prefixDirsPsr4    = ComposerStaticInitc70d3ae47e9b00f342b79455c7b671d9::$prefixDirsPsr4;
				$loader->classMap          = ComposerStaticInitc70d3ae47e9b00f342b79455c7b671d9::$classMap;

			},
			null,
			ClassLoader::class
		);
	}
}
