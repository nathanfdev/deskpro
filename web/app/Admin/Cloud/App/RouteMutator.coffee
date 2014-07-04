define [
	'DeskPRO/Util/Util'
], (
	Util
) ->
	routeModify = {
		"home":                           { route: {controller: 'Admin_Cloud_Main_Ctrl_Home'} }
		"license":                        { route: {controller: 'Admin_Cloud_License_Ctrl_License'} }
		"tickets.ticket_accounts.create": { route: {controller: 'Admin_Cloud_TicketAccounts_Ctrl_Edit'} }
		"tickets.ticket_accounts.edit":   { route: {controller: 'Admin_Cloud_TicketAccounts_Ctrl_Edit'} }
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
		{
			id: 'setup.cloud_custom_domain',
			url: '/cloud-custom-domain',
			templateName: 'Setup/custom-domain.html',
			controller: 'Admin_Cloud_Settings_Ctrl_CustomDomain'
		}
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