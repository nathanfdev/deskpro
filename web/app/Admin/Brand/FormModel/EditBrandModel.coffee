define [
	'DeskPRO/Util/Util'
], (Util) ->
	class Admin_Brand_FormModel_EditBrandModel
		constructor: (@brand) ->
			@form = {brand: {}}
			@form.brand.id = @brand.id || 0
			@form.brand.name = @brand.name || ''
			@form.brand.logo_blob = @brand.logo_blob


			if @brand.logo_blob
				@form.logo_set = 'current'
			else
				@form.logo_set = 'default'

		setBrandData: (data) ->
			@form.brand = data

		getFormData: ->
			form = Util.clone(@form, true)
			return form.brand
