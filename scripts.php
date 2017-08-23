<?php

return [
	
	/*
	 * Installation hook
	 *
	 */
	'install'   => function( $app ) {
		$util = $app[ 'db' ]->getUtility();
		if ( $util->tableExists( '@assets_asset' ) === false ) {
			$util->createTable(
				'@assets_asset',
				function( $table ) {
					$table->addColumn(
						'id',
						'integer',
						[
							'unsigned'      => true,
							'length'        => 10,
							'autoincrement' => true
						]
					);
					$table->addColumn( 'status', 'smallint' );
					$table->addColumn( 'slug', 'string', [ 'length' => 255 ] );
					$table->addColumn( 'title', 'string', [ 'length' => 255 ] );
					$table->addColumn( 'nodes', 'simple_array', [ 'notnull' => false ] );
					$table->addColumn( 'data', 'json_array', [ 'notnull' => false ] );
					$table->addColumn( 'date', 'datetime', [ 'notnull' => false ] );
					$table->addColumn( 'modified', 'datetime' );
					$table->setPrimaryKey( [ 'id' ] );
					$table->addUniqueIndex( [ 'slug' ], '@ASSETS_SLUG' );
				}
			);
		}
	},
	
	/*
	 * Enable hook
	 *
	 */
	'enable'    => function( $app ) {
	},
	
	/*
	 * Uninstall hook
	 *
	 */
	'uninstall' => function( $app ) {
		// remove the tables
		$util = $app[ 'db' ]->getUtility();
		if ( $util->tableExists( '@assets_asset' ) ) {
			$util->dropTable( '@assets_asset' );
		}
		
		// remove the config
		$app[ 'config' ]->remove( 'spqr/assets' );
	},
	
	/*
	 * Runs all updates that are newer than the current version.
	 *
	 */
	'updates'   => [],

];