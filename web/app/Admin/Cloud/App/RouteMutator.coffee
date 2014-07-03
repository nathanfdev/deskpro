define [
	'DeskPRO/Util/Util'
], (
	Util
) ->
	routeModify = {
		"license":                   { route: {controller: 'Admin_Cloud_License_Ctrl_License'} }
		"dev_ui":                    { cancel: true }
		"dev_ui_table":              { cancel: true }
		"twitter":                   { cancel: true }
		"server":                    { cancel: true }
		"setup.elastic_search":      { cancel: true }
		"server.server_settings":    { cancel: true }
		"server.server_reqs":        { cancel: true }
		"server.file_check":         { cancel: true }
		"server.file_uploads":       { cancel: true }
		"server.cron":               { cancel: true }
		"server.cron.logs":          { cancel: true }
		"server.php_info":           { cancel: true }
		"server.mysql_info":         { cancel: true }
		"server.mysql_status":       { cancel: true }
		"server.mysql_sort_order":   { cancel: true }
		"server.mysql_sort_order":   { cancel: true }
		"server.error_logs":         { cancel: true }
		"server.error_logs.view":    { cancel: true }
		"server.report_file":        { cancel: true }
	}

	newRoutes = [

	]

	class RouteMutator
		processRoute: (route) ->
			return route if not routeModify[route.id]?

			mod = routeModify[route.id]
			if mod.cancel
				return {
					id: route.id,
					url: route.url,
					templateName: "Index/blank.html",
					controller: ['$state', '$stateParams', ($state) -> $state.go('home')]
				}

			if mod.route
				if mod.overwrite
					return mod.route
				else
					return Util.merge(route, mod.route)

			return route

		getExtraRoutes: ->
			return newRoutes

	return RouteMutator