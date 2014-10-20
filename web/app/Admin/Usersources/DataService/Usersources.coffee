define [
	'Admin/Main/DataService/BaseListEdit'
], (
	BaseListEdit
) ->
	class Usersources extends BaseListEdit
		@$inject = ['Api', '$q']

		init: ->

		###
		# Update display orders
		#
		# @param {Array} Array of IDs in order
		# @return {promise}
		###
		saveDisplayOrder: (display_orders) ->
			@Api.sendPostJson('/usersources/display-order', {display_orders: display_orders})
