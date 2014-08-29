define ['angular'], (angular) ->
	class Admin_Labels_Services_LabelManager
		constructor: (Api, $q) ->
			@Api = Api
			@$q  = $q
			@label_types = {}
			@label_types_promises = {}

		loadLabels: (api_endpoint) ->
			if @label_types_promises[api_endpoint]
				return @label_types_promises[api_endpoint]

			d = @$q.defer()

			@Api.sendGet(api_endpoint).then( (result) =>
				@label_types[api_endpoint] = result.data.labels
				d.resolve(@label_types[api_endpoint])
			)

			@label_types_promises[api_endpoint] = d.promise
			return d.promise

		addLabel: (api_endpoint, label) ->
			@loadLabels(api_endpoint).then(=>
				@label_types[api_endpoint].push({
					label: label,
					count: 0
				})
			)

		renameLabel: (api_endpoint, old_label, new_label) ->
			@loadLabels(api_endpoint).then(=>
				dupe = false
				oldIdx = null
				for l, key in @label_types[api_endpoint]
					dupe = true if l.label == new_label
					oldIdx = key if l.label == old_label

				if dupe
					@label_types[api_endpoint].splice(oldIdx, 1)
				else if oldIdx
					@label_types[api_endpoint][oldIdx].label = new_label
			)

		removeLabel: (api_endpoint, label) ->
			@loadLabels(api_endpoint).then( (labels) =>
				idx = null
				for l, k in labels
					if l.label == label
						idx = k
						break

				if idx != null
					labels.splice(idx, 1)
			)