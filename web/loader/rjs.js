({
	"baseUrl": ".",
	"paths": {
		//@@include('paths')
	},
	"shims": {
		//@@include('shims')
	},
	"priority": [
		"jquery",
		"angular"
	],
	"preserveLicenseComments": false,
	"generateSourceMaps": true,
	"optimize": "uglify2",
	"uglify2": {
		mangle: false
	}
})
