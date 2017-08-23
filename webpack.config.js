module.exports = [
	{
		entry: {
			"settings": "./app/views/admin/settings.js",
			"asset-index": "./app/views/admin/asset-index",
			"asset-edit": "./app/views/admin/asset-edit"
		},
		output: {
			filename: "./app/bundle/[name].js"
		},
		module: {
			loaders: [
				{test: /\.vue$/, loader: "vue"},
				{test: /\.js$/, exclude: /node_modules/, loader: "babel-loader"}
			]
		}
	}
];