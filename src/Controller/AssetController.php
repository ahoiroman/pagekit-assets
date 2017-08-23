<?php

namespace Spqr\Assets\Controller;

use Pagekit\Application as App;
use Spqr\Assets\Model\Asset;


/**
 * @Access(admin=true)
 * @return string
 */
class AssetController
{
	/**
	 * @Access("assets: manage assets")
	 * @Request({"filter": "array", "page":"int"})
	 * @param null $filter
	 * @param int  $page
	 *
	 * @return array
	 */
	public function assetAction( $filter = null, $page = 0 )
	{
		return [
			'$view' => [ 'title' => 'Assets', 'name' => 'spqr/assets:views/admin/asset-index.php' ],
			'$data' => [
				'statuses' => Asset::getStatuses(),
				'config'   => [
					'filter'     => (object) $filter,
					'page'       => $page
				]
			]
		];
	}
	
	/**
	 * @Route("/asset/edit", name="asset/edit")
	 * @Access("assets: manage assets")
	 * @Request({"id": "int"})
	 * @param int $id
	 *
	 * @return array
	 */
	public function editAction( $id = 0 )
	{
		try {
			$module = App::module( 'spqr/assets' );
			
			if ( !$asset = Asset::where( compact( 'id' ) )->first() ) {
				if ( $id ) {
					App::abort( 404, __( 'Invalid asset id.' ) );
				}
				$asset = Asset::create(
					[
						'status' => Asset::STATUS_DRAFT,
						'date'   => new \DateTime()
					]
				);
				
				$asset->set( 'lang', 'js' );
				$asset->set( 'type', 'inline' );
				$asset->set( 'execution', 'immediately' );
				
			}
			
			return [
				'$view' => [
					'title' => $id ? __( 'Edit Asset' ) : __( 'Add Asset' ),
					'name'  => 'spqr/assets:views/admin/asset-edit.php'
				],
				'$data' => [
					'asset'   => $asset,
					'statuses' => Asset::getStatuses()
				]
			];
		} catch ( \Exception $e ) {
			App::message()->error( $e->getMessage() );
			
			return App::redirect( '@assets/asset' );
		}
	}
}