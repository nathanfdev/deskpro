define [
	'Admin/Main/DataService/BaseListEdit',
	'Admin/CustomFields/FieldFormMapper',
], (
	BaseListEdit,
	FieldFormMapper
)  ->
	class EntityFields extends BaseListEdit
		@$inject = ['Api', '$q']



		url: ->
			'/entity_fields'



		###
		# Update display orders
		#
		# @param {Array} Array of IDs in order
		# @return {promise}
		###
		saveDisplayOrder: (display_orders) ->
			@Api.sendPostJson('/entity_fields/display-order', {display_orders: display_orders})



		###
    	# Remove a field
    	#
    	# @param {Integer} id Filter id
    	# @return {promise}
		###
		deleteFieldById: (id) ->
			d = @$q.defer()
			@get(id).then (model) =>
				@remove model if model?
				d.resolve null
			d.promise




		###
    	# Get all data needed for the edit field page
    	#
    	# @param {Integer} id Filter id
    	# @return {promise}
		###
		loadEditFieldData: (id) ->
			deferred = @$q.defer()

			@get(id).then (res) =>
				field = res || {}
				deferred.resolve field: field, field_type: 0, form: field

			deferred.promise



		###
    	# Saves a form model and applies the form model to the field model
    	# once finished.
    	#
    	# @param {Object} fieldModel The field model
    	# @param {Object} formModel  The model representing the form
    	# @return {promise}
		###
		saveFormModel: (field) ->
			promise = @set field
			promise.success = (func) ->
				promise.then func
			promise.error = (func) ->
				promise.then null, func
			promise
