<?php

namespace Spqr\Assets\Model;

use Pagekit\Database\ORM\ModelTrait;

/**
 * Class AssetModelTrait
 * @package Spqr\Assets\Model
 */
trait AssetModelTrait
{
	use ModelTrait;
	
	
	/**
	 * @Saving
	 */
	public static function saving( $event, Asset $asset )
	{
		$asset->modified = new \DateTime();
		$i              = 2;
		$id             = $asset->id;
		while ( self::where( 'slug = ?', [ $asset->slug ] )->where(
			function( $query ) use ( $id ) {
				if ( $id ) {
					$query->where( 'id <> ?', [ $id ] );
				}
			}
		)->first() ) {
			$asset->slug = preg_replace( '/-\d+$/', '', $asset->slug ) . '-' . $i++;
		}
	}
	
}