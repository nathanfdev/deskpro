define ->
	class Admin_Main_Service_TemplateManager
		constructor: (@$templateCache, @$http, @$q) ->
			@pending = []
			@pendingNames = {}
			@sendPending = {}

		###
    	# Converts a template path into a common template name
    	# Eg: /deskpro/admin/load-view/Index/blank.html -> Index/blank.html
    	#
    	# @param {String} view
    	# @return {String}
		###
		commonName: (view) ->
			view = view.replace(/^.*?\/admin\/load\-view\//g, '')
			return view


		###
		# Mark a view to be loaded next time we are loading templates
    	#
    	# @param {String} view
		###
		load: (view) ->
			view = @commonName(view)
			if not @$templateCache.get(view) and not @pendingNames[view] and not @sendPending[view]
				@pending.push(view)
				@pendingNames[view] = true


		###
    	# Sets a template in the template cache
    	#
    	# @param {String} view
    	# @param {String} source
    	###
		setTemplate: (view, source) ->
			view = @commonName(view)
			@$templateCache.put(view, source)


		###
    	# Removes a template from the cache
    	#
    	# @param {String} view
    	###
		removeTemplate: (view) ->
			@$templateCache.remove(@commonName(view))


		###
    	# Execute the pending loads by ending the http request.
    	#
    	# @return {promise}
		###
		loadPending: ->
			if not @pending.length
				d = @$q.defer()
				d.resolve()
				return d.promise

			qs = []
			for t in @pending
				qs.push('views[]=' + encodeURIComponent(t))
			qs = qs.join('&')

			@pending = []
			@sendPending = @pendingNames
			@pendingNames = {}

			preloadTpls = @$http({
				method: 'GET',
				url: DP_BASE_ADMIN_URL+'/load-view/multi?' + qs
			})

			for own k, v of @sendPending
				@sendPending[k] = preloadTpls

			preloadTpls.success( (data) =>
				for tpl in data
					@$templateCache.put(tpl.id, tpl.source)

				for own k, v of @sendPending
					if v == preloadTpls
						@sendPending[k] = null
						delete @sendPending[k]
			)

			return preloadTpls


		###
    	# Gets the template source if it is already loaded, or null if it isnt
    	#
    	# @return {String|null}
		###
		getNow: (view) ->
			view = @commonName(view)
			tpl = @$templateCache.get(view)
			if tpl
				return tpl
			return null


		###
    	# Loads a template source along with any others that are queued.
    	#
    	# @return {promise}
		###
		get: (view) ->
			view = @commonName(view)
			exist = @$templateCache.get(view)
			if exist || exist == ""
				d = @$q.defer()
				d.resolve(@$templateCache.get(view))
				return d.promise

			if @sendPending[view]
				d = @$q.defer()
				@sendPending[view].then(=>
					d.resolve(@$templateCache.get(view))
				)
				return d.promise

			@load(view)
			promise = @loadPending()
			defer = @$q.defer()

			promise.then(=>
				tpl = @$templateCache.get(view)
				if tpl || tpl == ""
					defer.resolve(tpl)
				else
					console.log("Failed to load %s", view)
					defer.reject("failed")
			)

			return defer.promise

	return Admin_Main_Service_TemplateManager