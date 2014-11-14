define ->

  ($compile) ->

    templates =
      'com.atlassian.jira.plugin.system.customfieldtypes:textfield': """
        {{ field.name }}{{ field.required ? '*' : '' }}:
        <input type="text" name="{{ field.id }}" ng-model="value" ng-required="field.required" />
      """
      'com.atlassian.jira.plugin.system.customfieldtypes:textarea': """
        {{ field.name }}{{ field.required ? '*' : '' }}:
        <textarea name="{{ field.id }}" ng-model="value" ng-required="field.required"></textarea>
      """
      'com.atlassian.jira.plugin.system.customfieldtypes:select': """
        {{ field.name }}{{ field.required ? '*' : '' }}:
        <select  name="{{ field.id }}" ui-select2 ng-model="value" style="min-width: 200px;" ng-required="field.required" data-placeholder="Choose one">
          <option value=""></option>
          <option ng-repeat="val in field.allowedValues" value="{{ val.id }}">{{ field.schema.system ? val.name : val.value }}</option>
        </select>
      """
      'com.atlassian.jira.plugin.system.customfieldtypes:multiselect': """
        {{ field.name }}{{ field.required ? '*' : '' }}:
        <select name="{{ field.id }}" ui-select2 ng-model="value" style="min-width: 200px;" multiple ng-required="field.required">
          <option ng-repeat="val in field.allowedValues" value="{{ val.id }}">{{ field.schema.system ? val.name : val.value }}</option>
        </select>
      """
      'com.atlassian.jira.plugin.system.customfieldtypes:multicheckboxes': """
        {{ field.name }}{{ field.required ? '*' : '' }}:
        <label ng-repeat="val in field.allowedValues">
          <input name="{{ field.id }}" type="checkbox" value="{{ val.id }}" ng-required="field.required" />
          {{ field.schema.system ? val.name : val.value }}
        </label>
      """
      'com.atlassian.jira.plugin.system.customfieldtypes:radiobuttons': """
        {{ field.name }}{{ field.required ? '*' : '' }}:
        <label ng-repeat="val in field.allowedValues">
          <input name="{{ field.id }}" type="radio" name="{{ field.id }}" value="{{ val.id }}" ng-model="value" ng-required="field.required" />
          {{ field.schema.system ? val.name : val.value }}
        </label>
      """
      'com.atlassian.jira.plugin.system.customfieldtypes:labels': """
        {{ field.name }}{{ field.required ? '*' : '' }}:
        <input name="{{ field.id }}" type="text" ui-select2="{multiple: true, simple_tags: true, tags: []}" ng-model="value" ng-required="field.required" />
      """
      'com.atlassian.jira.plugin.system.customfieldtypes:datepicker': """
        {{ field.name }}{{ field.required ? '*' : '' }}:
        <div class="dropdown" style="display: inline-block">
          <a class="dropdown-toggle" role="button" data-toggle="dropdown" data-target="#" href="#">
            <div class="input-group">
              <input name="{{ field.id }}" type="text" class="form-control" ng-model="value" ng-required="field.required">
              <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
            </div>
          </a>
          <ul class="dropdown-menu">
            <datetimepicker ng-model="value" data-datetimepicker-config="{minView: 'day'}"/>
          </ul>
        </div>
      """
      'com.atlassian.jira.plugin.system.customfieldtypes:datetime': """
        {{ field.name }}{{ field.required ? '*' : '' }}:
        <div class="dropdown" style="display: inline-block">
          <a class="dropdown-toggle" role="button" data-toggle="dropdown" data-target="#" href="#">
            <div class="input-group">
              <input name="{{ field.id }}" type="text" class="form-control" ng-model="value" ng-required="field.required">
              <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
            </div>
          </a>
          <ul class="dropdown-menu">
            <datetimepicker ng-model="value" data-datetimepicker-config="{minView: 'minute'}"/>
          </ul>
        </div>
      """

    remap =
      description:        'com.atlassian.jira.plugin.system.customfieldtypes:textarea'
      duedate:            'com.atlassian.jira.plugin.system.customfieldtypes:datepicker'
      labels:             'com.atlassian.jira.plugin.system.customfieldtypes:labels'
      priority:           'com.atlassian.jira.plugin.system.customfieldtypes:select'
      resolution:         'com.atlassian.jira.plugin.system.customfieldtypes:select'
      resolutiondate:     'com.atlassian.jira.plugin.system.customfieldtypes:datetime'

    return {
      restrict: 'AE'
      scope:
        getField: '&field'
        model:    '=ngModel'
        error:    '='

      link: ($scope, $el, $attr) ->
        field = $scope.field = $scope.getField()
        return if !field.schema

        tpl = field.schema.custom
        tpl = field.schema.system if !tpl

        # fallback if no template found
        tpl = remap[tpl] || tpl
        tpl = 'com.atlassian.jira.plugin.system.customfieldtypes:textfield' if !templates[tpl]

        template = """
          <div class="input-group" ng-class="error && 'text-danger'" style="margin:10px auto;">#{templates[tpl]}</div>
        """
        $el.replaceWith $compile(template)($scope)

      controller: ($scope) ->
        types =
          'com.atlassian.jira.plugin.system.customfieldtypes:multiselect':      'array_objects'
          'com.atlassian.jira.plugin.system.customfieldtypes:multicheckboxes':  'array_objects'
          'com.atlassian.jira.plugin.system.customfieldtypes:select':           'object'
          'com.atlassian.jira.plugin.system.customfieldtypes:radiobuttons':     'object'
          'com.atlassian.jira.plugin.system.customfieldtypes:float':            'float'
          'com.atlassian.jira.plugin.system.customfieldtypes:datetime':         'datetime'
          priority:                                                             'object'
          resolution:                                                           'object'

        $scope.$watch 'value', (val) ->
          return $scope.model = val if !val?

          schema = $scope.getField().schema
          type = schema.custom || schema.system
          type = types[type]

          switch type
            when 'array_objects'
              val = val.map (item) -> {id: item}
            when 'object'
              val = {id: val}
            when 'float'
              val = parseFloat val
              val = null if NaN == val
            when 'datetime'
              val = moment(val).format('YYYY-MM-DDTHH:mm:ss.SSSZZ') if moment && val instanceof Date

          $scope.model = val


    }
