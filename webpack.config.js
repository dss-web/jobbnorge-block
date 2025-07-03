const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
	...defaultConfig,
	entry: {
		init: path.resolve(__dirname, 'src/init.js'),
		pagination: path.resolve(__dirname, 'src/pagination.js'),
	},
};
