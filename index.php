<?php

use Pagekit\Application as App;
use Spqr\Assets\Model\Asset;

return [
	'name' => 'spqr/assets',
	'type' => 'extension',
	'main' => function( $app ) {
	},
	
	'autoload' => [
		'Spqr\\Assets\\' => 'src'
	],
	
	'routes'  => [
		'/assets'     => [
			'name'       => '@assets',
			'controller' => [
				'Spqr\\Assets\\Controller\\AssetsController',
				'Spqr\\Assets\\Controller\\AssetController'
			]
		],
		'/api/assets' => [
			'name'       => '@assets/api',
			'controller' => [
				'Spqr\\Assets\\Controller\\AssetApiController'
			]
		]
	],
	'widgets' => [],
	
	'menu'        => [
		'assets'           => [
			'label'  => 'Assets',
			'url'    => '@assets/asset',
			'active' => '@assets/asset*',
			'icon'   => 'spqr/assets:icon.svg'
		],
		'assets: asset'    => [
			'parent' => 'assets',
			'label'  => 'Assets',
			'icon'   => 'spqr/assets:icon.svg',
			'url'    => '@assets/asset',
			'access' => 'assets: manage assets',
			'active' => '@assets/asset*'
		],
		'assets: settings' => [
			'parent' => 'assets',
			'label'  => 'Settings',
			'url'    => '@assets/settings',
			'access' => 'assets: manage settings'
		]
	],
	'permissions' => [
		'assets: manage settings' => [
			'title' => 'Manage settings'
		],
		'assets: manage assets'   => [
			'title' => 'Manage assets'
		]
	],
	
	'settings' => '@assets/settings',
	
	'resources' => [
		'spqr/assets:' => ''
	],
	
	'config' => [
		'caching'        => true,
		'items_per_page' => 20,
	],
	
	'events' => [
		'boot'         => function( $event, $app ) {
		},
		'site'         => function( $event, $app ) {
			$app->on(
				'view.content',
				function( $event, $scripts ) use ( $app ) {
					$config = $this->config;
					foreach ( $assets = Asset::where( [ 'status = ?' ], [ Asset::STATUS_PUBLISHED ] )->get() as $asset ) {
						if ( ( !$asset->nodes || in_array(
								$app[ 'node' ]->id,
								$asset->nodes
							) ) ) {
							
							$lang      = $asset->get( 'lang' );
							$type      = $asset->get( 'type' );
							$execution = $asset->get( 'execution' );
							$slug      = $asset->slug;
							$options   = [];
							
							if ( $config[ 'caching' ] && $type != 'url' ) {
								$hash                 = substr(
									sha1( $app->system()->config( 'secret' ) . date_format($asset->modified, 'd/m/Y H:i:s') ),
									0,
									4
								);
								$options[ 'version' ] = $hash;
							}
							
							if ( $type == 'url' ) {
								$content = $asset->get( 'url' );
							} elseif ( $type == 'inline' ) {
								$content = $asset->get( 'content' );
								$options = 'string';
							} elseif ( $type == 'file' ) {
								$content = 'spqr/assets:assets/custom/' . $asset->get( 'filename' );
							} else {
								$content = '';
							}
							if ( $lang == 'js' ) {
								if ( $type == 'url' || $type == 'file' ) {
									if ( $execution == 'deferred' ) {
										$options[ 'defer' ] = true;
									}
									if ( $execution == 'async' ) {
										$options[ 'async' ] = true;
									}
								}
								$app[ 'scripts' ]->add( $slug, $content, [], $options );
							} elseif ( $lang == 'css' ) {
								$app[ 'styles' ]->add( $slug, $content, [], $options );
							}
						}
					}
				}
			);
			
		}
	]
];