define ['Admin/Main/Util/EventsMixin'], (EventsMixin) ->
	class AppState
		constructor: (@$rootScope, @$state) ->
			EventsMixin(this)
			@vars = {}