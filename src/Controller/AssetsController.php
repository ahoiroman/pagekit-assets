<?php

namespace Spqr\Assets\Controller;

use Pagekit\Application as App;

/**
 * @Access(admin=true)
 * @return string
 */
class AssetsController
{
	/**
	 * @Access("assets: manage settings")
	 */
	public function settingsAction()
	{
		return [
			'$view' => [
				'title' => __( 'Assets Settings' ),
				'name'  => 'spqr/assets:views/admin/settings.php'
			],
			'$data' => [
				'config' => App::module( 'spqr/assets' )->config()
			]
		];
	}
	
	/**
	 * @Request({"config": "array"}, csrf=true)
	 * @param array $config
	 *
	 * @return array
	 */
	public function saveAction( $config = [] )
	{
		App::config()->set( 'spqr/assets', $config );
		
		return [ 'message' => 'success' ];
	}
	
}