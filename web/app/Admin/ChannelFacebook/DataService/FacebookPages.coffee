define [
	'Admin/Main/DataService/Base',
	'Admin/Main/Model/Base',
	'Admin/Main/Collection/OrderedDictionary'
], (Admin_Main_DataService_Base,
	Admin_Main_Model_Base,
	Admin_Main_Collection_OrderedDictionary)  ->
	class Admin_ChannelFacebook_DataService_FacebookPages extends Admin_Main_DataService_Base
		constructor: (em, Api, $q) ->
			super(em)
			@$q = $q
			@Api = Api

			@loadListPromise = null
			@recs = new Admin_Main_Collection_OrderedDictionary()

		###*
		* Loads list of accounts
		*
		* @return {Promise}
		###
		loadList: (reload) ->
			if @loadListPromise
				return @loadListPromise

			deferred = @$q.defer()
			if not reload and @recs.count()
				deferred.resolve(@recs)
				return deferred.promise

			http_def = @Api.sendGet('/channel/facebook/pages').success((data, status, headers, config) =>
				@_setListData(data.facebook_pages)
				deferred.resolve(@recs)
			, (data, status, headers, config) ->
				deferred.reject()
			)

			@loadListPromise = deferred.promise

			return @loadListPromise

		remove: (id) ->
			@recs.remove(id)
			@em.removeById('facebook_page', 'id')

		_setListData: (raw_recs) ->
			for rec in raw_recs
				model = @em.createEntity('facebook_page', 'id', rec)
				model.retain()
				@recs.set(model.id, model)

		checkExistsByGraphId: (graph_id) ->
			found_it = false
			@recs.forEach( (id, rec) ->
				if rec.graph_id == graph_id
					found_it = true
			)
			return found_it

		###
		# Updates the first-class model (title, etc)
		# with page provided. Or adds it to the list if it doesnt exist.
		###
		updateModel: (page) ->
			new_model = @em.createEntity('facebook_page', 'id', page)
			@recs.set(new_model.id, new_model)
			return new_model

		###
		# Adds a new model to the existing list (eg was just created)
		#
		# @return {Admin_Main_Model_Base}
		###
		addToList: (rec) ->
			if not rec._is_model
				model = @em.createEntity('facebook_page', 'id', rec)
			else
				model = @em.add(rec, true)

			@recs.set(model.id, model)
			return model
