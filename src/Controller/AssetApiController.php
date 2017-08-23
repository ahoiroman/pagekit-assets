<?php

namespace Spqr\Assets\Controller;

use Pagekit\Application as App;
use Pagekit\Application\Exception;
use Spqr\Assets\Model\Asset;

/**
 * @Access("assets: manage assets")
 * @Route("asset", name="asset")
 */
class AssetApiController
{
	
	/**
	 * @param array $filter
	 * @param int   $page
	 * @param int   $limit
	 * @Route("/", methods="GET")
	 * @Request({"filter": "array", "page":"int", "limit":"int"})
	 *
	 * @return mixed
	 */
	public function indexAction( $filter = [], $page = 0, $limit = 0 )
	{
		$query  = Asset::query();
		$filter = array_merge( array_fill_keys( [ 'status', 'search', 'limit', 'order' ], '' ), $filter );
		extract( $filter, EXTR_SKIP );
		if ( is_numeric( $status ) ) {
			$query->where( [ 'status' => (int) $status ] );
		}
		if ( $search ) {
			$query->where(
				function( $query ) use ( $search ) {
					$query->orWhere(
						[
							'title LIKE :search'
						],
						[ 'search' => "%{$search}%" ]
					);
				}
			);
		}
		if ( preg_match( '/^(title|date|slug)\s(asc|desc)$/i', $order, $match ) ) {
			$order = $match;
		} else {
			$order = [ 1 => 'title', 2 => 'asc' ];
		}
		$default = App::module( 'spqr/assets' )->config( 'items_per_page' );
		$limit   = min( max( 0, $limit ), $default ) ? : $default;
		$count   = $query->count();
		$pages   = ceil( $count / $limit );
		$page    = max( 0, min( $pages - 1, $page ) );
		$assets  = array_values(
			$query->offset( $page * $limit )->limit( $limit )->orderBy( $order[ 1 ], $order[ 2 ] )->get()
		);
		
		return compact( 'assets', 'pages', 'count' );
	}
	
	/**
	 * @Route("/{id}", methods="GET", requirements={"id"="\d+"})
	 * @param $id
	 *
	 * @return static
	 */
	public function getAction( $id )
	{
		if ( !$asset = Asset::where( compact( 'id' ) )->first() ) {
			App::abort( 404, 'Asset not found.' );
		}
		
		
		return $asset;
	}
	
	/**
	 * @Route(methods="POST")
	 * @Request({"ids": "int[]"}, csrf=true)
	 * @param array $ids
	 *
	 * @return array
	 */
	public function copyAction( $ids = [] )
	{
		foreach ( $ids as $id ) {
			if ( $asset = Asset::find( (int) $id ) ) {
				if ( !App::user()->hasAccess( 'assets: manage assets' ) ) {
					continue;
				}
				$asset         = clone $asset;
				$asset->id     = null;
				$asset->status = $asset::STATUS_UNPUBLISHED;
				$asset->title  = $asset->title . ' - ' . __( 'Copy' );
				$asset->date   = new \DateTime();
				$asset->save();
			}
		}
		
		return [ 'message' => 'success' ];
	}
	
	/**
	 * @Route("/bulk", methods="POST")
	 * @Request({"assets": "array"}, csrf=true)
	 * @param array $assets
	 *
	 * @return array
	 */
	public function bulkSaveAction( $assets = [] )
	{
		foreach ( $assets as $data ) {
			$this->saveAction( $data, isset( $data[ 'id' ] ) ? $data[ 'id' ] : 0 );
		}
		
		return [ 'message' => 'success' ];
	}
	
	
	/**
	 * @Route("/", methods="POST")
	 * @Route("/{id}", methods="POST", requirements={"id"="\d+"})
	 * @Request({"asset": "array", "id": "int"}, csrf=true)
	 *
	 * @param     $data
	 * @param int $id
	 *
	 * @return array
	 */
	public function saveAction( $data, $id = 0 )
	{
		if ( !$id || !$asset = Asset::find( $id ) ) {
			if ( $id ) {
				App::abort( 404, __( 'Asset not found.' ) );
			}
			$asset = Asset::create();
		}
		if ( !$data[ 'slug' ] = App::filter( $data[ 'slug' ] ? : $data[ 'title' ], 'slugify' ) ) {
			App::abort( 400, __( 'Invalid slug.' ) );
		}
		
		if ( $data[ 'data' ][ 'type' ] == 'file' ) {
			if ( !empty( $content = $data[ 'data' ][ 'content' ] ) ) {
				$filecontent = $content;
			} else {
				$filecontent = $asset->data[ 'content' ];
			}
			
			if ( empty( $data[ 'data' ][ 'filename' ] ) && empty( $asset->data[ 'filename' ] ) ) {
				$data[ 'data' ][ 'filename' ] = $data[ 'slug' ] . "." . $data[ 'data' ][ 'lang' ];
			} elseif ( !empty( $data[ 'data' ][ 'filename' ] ) ) {
				$data[ 'data' ][ 'filename' ] = $data[ 'data' ][ 'filename' ];
			} elseif ( !empty( $asset->data[ 'filename' ] ) ) {
				$data[ 'data' ][ 'filename' ] = $asset->data[ 'filename' ];
			}
			
			$filename = $data[ 'data' ][ 'filename' ];
			
			if ( !empty( $data[ 'data' ][ 'filename' ] ) && !empty( $asset->data[ 'filename' ] ) && $asset->data[ 'filename' ] != $data[ 'data' ][ 'filename' ] ) {
				try {
					App::file()->delete(
						App::locator()->get( 'spqr/assets:assets/custom/' . $asset->data[ 'filename' ] )
					);
				} catch ( \Exception $e ) {
					throw new Exception( __( 'Unable to delete old file.' ) );
				}
			}
			
			try {
				$path = App::locator()->get( 'spqr/assets:assets/custom/' );
				if ( !$path ) {
					try {
						App::file()->makeDir(
							App::locator()->get( 'spqr/assets:' ) . DIRECTORY_SEPARATOR . 'assets/custom'
						);
						$path = App::locator()->get( 'spqr/assets:assets/custom/' );
					} catch ( \Exception $e ) {
						throw new Exception( __( 'Unable to create asset directory.' ) );
					}
				}
				$file = file_put_contents( $path . $filename, $filecontent );
			} catch ( \Exception $e ) {
				throw new Exception( __( 'Unable to create file.' ) );
			}
		} elseif ( $data[ 'data' ][ 'type' ] == 'url' || $data[ 'data' ][ 'type' ] == 'inline' ) {
			if ( !empty( $asset->data[ 'filename' ] ) ) {
				try {
					$path = App::locator()->get( 'spqr/assets:assets/custom/' . $asset->data[ 'filename' ] );
					App::file()->delete( $path );
				} catch ( \Exception $e ) {
					throw new Exception( __( 'Unable to delete old file.' ) );
				}
			}
			
			unset( $data[ 'data' ][ 'filename' ] );
		}
		
		$asset->save( $data );
		
		return [ 'message' => 'success', 'asset' => $asset ];
	}
	
	/**
	 * @Route("/bulk", methods="DELETE")
	 * @Request({"ids": "array"}, csrf=true)
	 * @param array $ids
	 *
	 * @return array
	 */
	public function bulkDeleteAction( $ids = [] )
	{
		foreach ( array_filter( $ids ) as $id ) {
			$this->deleteAction( $id );
		}
		
		return [ 'message' => 'success' ];
	}
	
	/**
	 * @Route("/{id}", methods="DELETE", requirements={"id"="\d+"})
	 * @Request({"id": "int"}, csrf=true)
	 * @param $id
	 *
	 * @return array
	 */
	public function deleteAction( $id )
	{
		if ( $asset = Asset::find( $id ) ) {
			if ( !App::user()->hasAccess( 'assets: manage assets' ) ) {
				App::abort( 400, __( 'Access denied.' ) );
			}
			
			if ( $asset->data[ 'type' ] == 'file' ) {
				if ( !empty( $asset->data[ 'filename' ] ) ) {
					try {
						$path = App::locator()->get( 'spqr/assets:assets/custom/' . $asset->data[ 'filename' ] );
						if ( $path ) {
							App::file()->delete( $path );
						}
					} catch ( \Exception $e ) {
						throw new Exception( __( 'Unable to delete old file.' ) );
					}
				}
			}
			
			$asset->delete();
		}
		
		return [ 'message' => 'success' ];
	}
	
}