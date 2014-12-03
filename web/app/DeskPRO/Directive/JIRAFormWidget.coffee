define ->

	($compile) ->

		templates =
			'com.atlassian.jira.plugin.system.customfieldtypes:textfield': """
        <input type="text" ng-model="value" ng-required="field.required" />
      """
			'com.atlassian.jira.plugin.system.customfieldtypes:textarea': """
        <textarea ng-model="value" ng-required="field.required"></textarea>
      """
			'com.atlassian.jira.plugin.system.customfieldtypes:select': """
        <select  ui-select2 ng-model="value" style="min-width: 200px;" ng-required="field.required" data-placeholder="Choose one">
          <option value=""></option>
          <option ng-repeat="val in field.allowedValues" ng-value="val.id">{{ field.schema.system ? val.name : val.value }}</option>
        </select>
      """
			'com.atlassian.jira.plugin.system.customfieldtypes:multiselect': """
        <select ui-select2 ng-model="value" style="min-width: 200px;" multiple ng-required="field.required">
          <option ng-repeat="val in field.allowedValues" ng-value="val.id">{{ field.schema.system ? val.name : val.value }}</option>
        </select>
      """
			'com.atlassian.jira.plugin.system.customfieldtypes:multicheckboxes': """
        <label ng-repeat="val in field.allowedValues" ng-init="$parent.value = []">
          <input type="checkbox" ng-value="val.id"
              ng-checked="$parent.value.indexOf(val.id) > -1"
              ng-click="checkboxToggle(val.id)"
              />
          {{ field.schema.system ? val.name : val.value }}
        </label>
      """
			'com.atlassian.jira.plugin.system.customfieldtypes:radiobuttons': """
        <label ng-repeat="val in field.allowedValues">
          <input name="{{ field.id }}" type="radio" ng-value="val.id" ng-model="$parent.value" />
          {{ field.schema.system ? val.name : val.value }}
        </label>
      """
			'com.atlassian.jira.plugin.system.customfieldtypes:labels': """
        <input type="text" ui-select2="{multiple: true, simple_tags: true, tags: []}" ng-model="value" ng-required="field.required" />
      """
			'com.atlassian.jira.plugin.system.customfieldtypes:datepicker': """
				<input name="{{ field.id }}" type="text" ng-model="value" dp-datetime-input="{minView: 'day'}" readonly />
      """
			'com.atlassian.jira.plugin.system.customfieldtypes:datetime': """
				<input name="{{ field.id }}" type="text" ng-model="value" dp-datetime-input="{minView: 'minute'}" readonly />
      """

		remap =
			description: 'com.atlassian.jira.plugin.system.customfieldtypes:textarea'
			duedate: 'com.atlassian.jira.plugin.system.customfieldtypes:datepicker'
			labels: 'com.atlassian.jira.plugin.system.customfieldtypes:labels'
			priority: 'com.atlassian.jira.plugin.system.customfieldtypes:select'
			resolution: 'com.atlassian.jira.plugin.system.customfieldtypes:select'
			resolutiondate: 'com.atlassian.jira.plugin.system.customfieldtypes:datetime'
			created: 'com.atlassian.jira.plugin.system.customfieldtypes:datetime'
			updated: 'com.atlassian.jira.plugin.system.customfieldtypes:datetime'

		restrict: 'AE'
		scope:
			getField: '&field'
			model: '=ngModel'
			error: '='

		link: ($scope, $el, $attr) ->
			field = $scope.field = $scope.getField()
			return if not field?.schema

			tpl = field.schema.custom
			tpl = field.schema.system if !tpl

			# fallback if no template found
			tpl = remap[tpl] || tpl
			tpl = 'com.atlassian.jira.plugin.system.customfieldtypes:textfield' if !templates[tpl]

			$template = $ """
          <div class="input-group" ng-class="error && 'text-danger'" style="margin:10px auto;">
            #{templates[tpl]}
          </div>
        """

			# map value back from model format
			types =
				'com.atlassian.jira.plugin.system.customfieldtypes:multiselect': 'array_objects'
				'com.atlassian.jira.plugin.system.customfieldtypes:multicheckboxes': 'array_objects'
				'com.atlassian.jira.plugin.system.customfieldtypes:select': 'object'
				'com.atlassian.jira.plugin.system.customfieldtypes:radiobuttons': 'object'
				'com.atlassian.jira.plugin.system.customfieldtypes:float': 'float'
				'com.atlassian.jira.plugin.system.customfieldtypes:datepicker': 'date'
				'com.atlassian.jira.plugin.system.customfieldtypes:datetime': 'date'
				priority: 'object'
				resolution: 'object'
				parent: (val) -> val.key if val

			value = $scope.model
			if value
				schema = field.schema
				type = schema.custom || schema.system
				type = types[type] || types[remap[type]]

				switch type
					when 'array_objects'
						value = value.map (item) -> item.id
					when 'object'
						value = value.id
					when 'date'
						value = moment(value).getDate() if moment

				if 'function' == typeof type
					value = type(value)

			$scope.value = value
			$el.replaceWith $compile($template)($scope)

		controller: ($scope) ->
			types =
				'com.atlassian.jira.plugin.system.customfieldtypes:multiselect': 'array_objects'
				'com.atlassian.jira.plugin.system.customfieldtypes:multicheckboxes': 'array_objects'
				'com.atlassian.jira.plugin.system.customfieldtypes:select': 'object'
				'com.atlassian.jira.plugin.system.customfieldtypes:radiobuttons': 'object'
				'com.atlassian.jira.plugin.system.customfieldtypes:float': 'float'
				'com.atlassian.jira.plugin.system.customfieldtypes:datepicker': 'date'
				'com.atlassian.jira.plugin.system.customfieldtypes:datetime': 'datetime'
				duedate: 'date'
				priority: 'object'
				resolution: 'object'
				parent: (val) -> { key: val }

			mapModel = (val) ->
				return $scope.model = val if !val?

				schema = $scope.getField().schema
				type = schema.custom || schema.system
				type = types[type] || types[remap[type]]

				switch type
					when 'array_objects'
						val = val.map (item) -> { id: item }
					when 'object'
						val = { id: val }
					when 'float'
						val = parseFloat val
						val = null if NaN == val
					when 'date'
						val = new Date(val) if !(val instanceof Date)
						val = moment(val).format('YYYY-MM-DD') if moment
					when 'datetime'
						val = new Date(val) if !(val instanceof Date)
						val = moment(val).format('YYYY-MM-DDTHH:mm:ss.SSSZZ') if moment

				if 'function' == typeof type
					val = type(val)

				$scope.model = val

			$scope.$watch 'value', mapModel

			$scope.checkboxToggle = (val) ->
				return if !val?
				idx = $scope.value.indexOf val
				if idx > -1 then $scope.value.splice(idx, 1) else $scope.value.push val
				mapModel $scope.value # trigger 'watch' manually
