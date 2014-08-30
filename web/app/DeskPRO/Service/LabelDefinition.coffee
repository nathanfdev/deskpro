define ['angular'], (angular) ->
	class DeskPRO_Service_LabelDefinition
		loadDefinitions = null
		updateColorForLabel = null
		defaultColor = '#d4d4d4'

		constructor: (@$q, definitionsPromise) ->
			loadPromise = null
			@definitions =
				tickets: {}
				people: {}
				organizations: {}
				news: {}
				kb: {}
				feedback: {}
				downloads: {}
				chat: {}

			@colors = {} # global to all label_types

			loadDefinitions = =>
				return loadPromise if loadPromise
				d = @$q.defer()

				definitionsPromise.then (data) =>

					for def in data.data
						continue if !def.label? || !def.label_type?
						label = def.label.toLowerCase()
						@definitions[def.label_type] = @definitions[def.label_type] || {}
						@definitions[def.label_type][label] = angular.copy def
						@colors[label] = def.color

					d.resolve @definitions

				loadPromise = d.promise

			updateColorForLabel = (color, label) =>
				label = label.toLowerCase()
				for label_type of @definitions
					for _label of @definitions[label_type]
						if _label == label
							@definitions[label_type][_label].color = color
							@colors[label] = color



		all: (label_type) =>
			d = @$q.defer()
			loadDefinitions().then => d.resolve @definitions[label_type]
			d.promise



		get: (label_type, label) ->
			d = @$q.defer()
			label = (label || '').toLowerCase()
			loadDefinitions().then => d.resolve @definitions[label_type]?[label]
			d.promise



		update: (_old, _new) ->
			return if !_new.label || !_new.label_type || !_new.color
			label = _new.label.toLowerCase()

			if _old
				delete @definitions[_old.label_type][_old.label.toLowerCase()]
				delete @colors[_old.label.toLowerCase()]

			@definitions[_new.label_type] = @definitions[_new.label_type] || {}
			@definitions[_new.label_type][label] = _new

			# update colors for all definition label_types and for @colors
			updateColorForLabel _new.color, label



		remove: (def) ->
			return if !def.label || !def.label_type
			if @definitions[def.label_type]?[def.label.toLowerCase()]?
				delete @definitions[def.label_type][def.label.toLowerCase()]
				delete @colors[def.label.toLowerCase()]
			updateColorForLabel def.color, def.label



		getColor: (label) ->
			d = @$q.defer()
			label = (label || '').toLowerCase()
			loadDefinitions().then => d.resolve @colors[label] || defaultColor
			d.promise
