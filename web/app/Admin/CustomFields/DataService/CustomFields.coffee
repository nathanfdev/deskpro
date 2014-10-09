define [
	'Admin/Main/DataService/BaseListEdit',
], (
	BaseListEdit
)  ->
	class Admin_CustomFields_DataService_CustomFields extends BaseListEdit
		@$inject = ['Api', '$q']



		# todo: option to specify field type
		url: ->
			'/custom_fields'



		# todo: option to specify owner and context
		_doLoadList: ->
			deferred = @$q.defer()

			@Api.sendGet(@url(), {owner: 'ticket', context: 'person'}).then (res) ->
				deferred.resolve res.data || []

			deferred.promise



		###
		# Update display orders
		#
		# @param {Array} Array of IDs in order
		# @return {promise}
		###
		saveDisplayOrder: (display_orders) ->
			@Api.sendPostJson('/custom_fields/display-order', {display_orders: display_orders})
