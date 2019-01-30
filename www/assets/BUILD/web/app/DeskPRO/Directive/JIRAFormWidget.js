// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(() =>

  function($compile) {

    const templates = {
      'com.atlassian.jira.plugin.system.customfieldtypes:textfield': `\
<input type="text" ng-model="value" ng-required="field.required" />\
`,
      'com.atlassian.jira.plugin.system.customfieldtypes:textarea': `\
<textarea ng-model="value" ng-required="field.required"></textarea>\
`,
      'com.atlassian.jira.plugin.system.customfieldtypes:select': `\
<select  ui-select2 ng-model="value" style="min-width: 200px;" ng-required="field.required" data-placeholder="Choose one">
  <option value=""></option>
  <option ng-repeat="val in field.allowedValues" ng-value="val.id">{{ field.schema.system ? val.name : val.value }}</option>
</select>\
`,
      'com.atlassian.jira.plugin.system.customfieldtypes:multiselect': `\
<select ui-select2 ng-model="value" style="min-width: 200px;" multiple ng-required="field.required">
  <option ng-repeat="val in field.allowedValues" ng-value="val.id">{{ field.schema.system ? val.name : val.value }}</option>
</select>\
`,
      'com.atlassian.jira.plugin.system.customfieldtypes:multicheckboxes': `\
<label ng-repeat="val in field.allowedValues" ng-init="$parent.value = []">
  <input type="checkbox" ng-value="val.id"
      ng-checked="$parent.value.indexOf(val.id) > -1"
      ng-click="checkboxToggle(val.id)"
      />
  {{ field.schema.system ? val.name : val.value }}
</label>\
`,
      'com.atlassian.jira.plugin.system.customfieldtypes:radiobuttons': `\
<label ng-repeat="val in field.allowedValues">
  <input name="{{ field.id }}" type="radio" ng-value="val.id" ng-model="$parent.value" />
  {{ field.schema.system ? val.name : val.value }}
</label>\
`,
      'com.atlassian.jira.plugin.system.customfieldtypes:labels': `\
<input type="text" ui-select2="{multiple: true, simple_tags: true, tags: []}" ng-model="value" ng-required="field.required" />\
`,
      'com.atlassian.jira.plugin.system.customfieldtypes:datepicker': `\
<input name="{{ field.id }}" type="text" ng-model="value" dp-datetime-popup="DD MMM YYYY" append-to="body" readonly />\
`,
      'com.atlassian.jira.plugin.system.customfieldtypes:datetime': `\
<input name="{{ field.id }}" type="text" ng-model="value" dp-datetime-popup="DD MMM YYYY HH:mm" append-to="body" readonly />\
`
    };

    const remap = {
      description: 'com.atlassian.jira.plugin.system.customfieldtypes:textarea',
      duedate: 'com.atlassian.jira.plugin.system.customfieldtypes:datepicker',
      labels: 'com.atlassian.jira.plugin.system.customfieldtypes:labels',
      priority: 'com.atlassian.jira.plugin.system.customfieldtypes:select',
      resolution: 'com.atlassian.jira.plugin.system.customfieldtypes:select',
      resolutiondate: 'com.atlassian.jira.plugin.system.customfieldtypes:datetime',
      created: 'com.atlassian.jira.plugin.system.customfieldtypes:datetime',
      updated: 'com.atlassian.jira.plugin.system.customfieldtypes:datetime',
      components: 'com.atlassian.jira.plugin.system.customfieldtypes:multiselect',
      versions:   'com.atlassian.jira.plugin.system.customfieldtypes:multiselect'
    };

    return {
      restrict: 'AE',
      scope: {
        getField: '&field',
        model: '=ngModel',
        error: '='
      },

      link($scope, $el, $attr) {
        const field = ($scope.field = $scope.getField());
        if (!(field != null ? field.schema : undefined)) { return; }

        let tpl = field.schema.custom;
        if (!tpl) { tpl = field.schema.system; }

        // fallback if no template found
        tpl = remap[tpl] || tpl;
        if (!templates[tpl]) { tpl = 'com.atlassian.jira.plugin.system.customfieldtypes:textfield'; }

        const $template = $(`\
<div class="input-group" ng-class="error && 'text-danger'" style="margin:10px auto;">
    ${templates[tpl]}
</div>\
`
        );

        // map value back from model format
        const types = {
          'com.atlassian.jira.plugin.system.customfieldtypes:multiselect': 'array_objects',
          'com.atlassian.jira.plugin.system.customfieldtypes:multicheckboxes': 'array_objects',
          'com.atlassian.jira.plugin.system.customfieldtypes:select': 'object',
          'com.atlassian.jira.plugin.system.customfieldtypes:radiobuttons': 'object',
          'com.atlassian.jira.plugin.system.customfieldtypes:float': 'float',
          'com.atlassian.jira.plugin.system.customfieldtypes:datepicker': 'date',
          'com.atlassian.jira.plugin.system.customfieldtypes:datetime': 'date',
          priority: 'object',
          resolution: 'object',
          parent(val) { if (val) { return val.key; } }
        };

        let value = $scope.model;
        if (value) {
          const { schema } = field;
          let type = schema.custom || schema.system;
          type = types[type] || types[remap[type]];

          switch (type) {
            case 'array_objects':
              value = value.map(item => item.id);
              break;
            case 'object':
              value = value.id;
              break;
            case 'date':
              if (moment) { value = moment(value).getDate(); }
              break;
          }

          if ('function' === typeof type) {
            value = type(value);
          }
        }

        $scope.value = value;
        return $el.replaceWith($compile($template)($scope));
      },

      controller($scope) {
        const types = {
          'com.atlassian.jira.plugin.system.customfieldtypes:multiselect': 'array_objects',
          'com.atlassian.jira.plugin.system.customfieldtypes:multicheckboxes': 'array_objects',
          'com.atlassian.jira.plugin.system.customfieldtypes:select': 'object',
          'com.atlassian.jira.plugin.system.customfieldtypes:radiobuttons': 'object',
          'com.atlassian.jira.plugin.system.customfieldtypes:float': 'float',
          'com.atlassian.jira.plugin.system.customfieldtypes:datepicker': 'date',
          'com.atlassian.jira.plugin.system.customfieldtypes:datetime': 'datetime',
          duedate: 'date',
          priority: 'object',
          resolution: 'object',
          parent(val) { return { key: val }; }
        };

        const mapModel = function(val) {
          if ((val == null)) { return $scope.model = val; }

          const { schema } = $scope.getField();
          let type = schema.custom || schema.system;
          type = types[type] || types[remap[type]];

          switch (type) {
            case 'array_objects':
              val = val.map(item => ({ id: item }));
              break;
            case 'object':
              val = { id: val };
              break;
            case 'float':
              val = parseFloat(val);
              if (NaN === val) { val = null; }
              break;
            case 'date':
              if (!(val instanceof Date)) { val = new Date(val); }
              if (moment) { val = moment(val).format('YYYY-MM-DD'); }
              break;
            case 'datetime':
              if (!(val instanceof Date)) { val = new Date(val); }
              if (moment) { val = moment(val).format('YYYY-MM-DDTHH:mm:ss.SSSZZ'); }
              break;
          }

          if ('function' === typeof type) {
            val = type(val);
          }

          return $scope.model = val;
        };

        $scope.$watch('value', mapModel);

        return $scope.checkboxToggle = function(val) {
          if ((val == null)) { return; }
          const idx = $scope.value.indexOf(val);
          if (idx > -1) { $scope.value.splice(idx, 1); } else { $scope.value.push(val); }
          return mapModel($scope.value);
        };
      }
    };
  }
); // trigger 'watch' manually
