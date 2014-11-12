requirejs.config({
	"baseUrl":     DP_ASSET_URL,
	"waitSeconds": 60,
	"urlArgs":    (DP_IS_DEBUG ? "bust=" + (new Date()).getTime() : "v=" + (DP_BUILD_TIME || "0")),
	"paths": {
		//@@include('paths')
	},
	"shims": {
		//@@include('shims')
	},
	"priority": [
		"jquery",
		"angular"
	]
});
