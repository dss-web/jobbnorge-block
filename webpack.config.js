const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: {
		init: path.resolve( __dirname, 'src/init.js' ),
		editor: path.resolve( __dirname, 'src/editor.scss' ),
		style: path.resolve( __dirname, 'src/style.scss' ),
		pagination: path.resolve( __dirname, 'src/pagination.js' ),
	},
};
