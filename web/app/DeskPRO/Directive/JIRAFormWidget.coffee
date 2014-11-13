define ->

	($compile) ->

    templates =
      "input": """
        {{ field.name }}<span ng-if="field.required">*</span>:
        <input type="text" ng-model="ngModel" />
      """
      "com.atlassian.jira.plugin.system.customfieldtypes:select": """
        {{ field.name }}<span ng-if="field.required">*</span>:
        <select ui-select2 ng-model="ngModel" style="min-width: 200px;">
          <option ng-repeat="val in field.allowedValues" value="{{ val.id }}">{{ val.value }}</option>
        </select>
      """
      "com.atlassian.jira.plugin.system.customfieldtypes:multiselect": """
        {{ field.name }}<span ng-if="field.required">*</span>:
        <select ui-select2 ng-model="ngModel" style="min-width: 200px;" multiple>
          <option ng-repeat="val in field.allowedValues" value="{{ val.id }}">{{ val.value }}</option>
        </select>
        {{ ngModel }}
      """
      "com.atlassian.jira.plugin.system.customfieldtypes:multicheckboxes": """
        {{ field.name }}<span ng-if="field.required">*</span>:
        <label ng-repeat="val in field.allowedValues">
          <input type="checkbox" name="{{ field.id }}[]" value="{{ val.id }}" /> {{ val.value }}
        </label>
      """
      "com.atlassian.jira.plugin.system.customfieldtypes:radiobuttons": """
        {{ field.name }}<span ng-if="field.required">*</span>:
        <label ng-repeat="val in field.allowedValues">
          <input type="radio" name="{{ field.id }}" value="{{ val.id }}" /> {{ val.value }}
        </label>
      """
      "duedate": """
        {{ field.name }}<span ng-if="field.required">*</span>:
        <input type="date" ng-model="ngModel" />
      """

    return {
      restrict: 'AE'
      scope:
        getField: '&field'
        ngModel : '='

      link: ($scope, $el, $attr) ->
#        console.info $el
        field = $scope.field = $scope.getField()
#        console.info field
        return if !field.schema

        tpl = field.schema.custom
        tpl = field.schema.system if !tpl
        tpl = 'input' if !tpl
#        console.info tpl
        return if !templates[tpl]

#        console.info tpl
        template = "<div style='margin:10px auto;'>#{templates[tpl]}</div>"
        $el.replaceWith $compile(template)($scope)
    }
