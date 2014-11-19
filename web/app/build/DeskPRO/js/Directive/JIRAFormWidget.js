(function() {
  define(function() {
    return function($compile) {
      var remap, templates;
      templates = {
        'com.atlassian.jira.plugin.system.customfieldtypes:textfield': "{{ field.name }}{{ field.required ? '*' : '' }}:\n<input type=\"text\" ng-model=\"value\" ng-required=\"field.required\" />",
        'com.atlassian.jira.plugin.system.customfieldtypes:textarea': "{{ field.name }}{{ field.required ? '*' : '' }}:\n<textarea ng-model=\"value\" ng-required=\"field.required\"></textarea>",
        'com.atlassian.jira.plugin.system.customfieldtypes:select': "{{ field.name }}{{ field.required ? '*' : '' }}:\n<select  ui-select2 ng-model=\"value\" style=\"min-width: 200px;\" ng-required=\"field.required\" data-placeholder=\"Choose one\">\n  <option value=\"\"></option>\n  <option ng-repeat=\"val in field.allowedValues\" ng-value=\"val.id\">{{ field.schema.system ? val.name : val.value }}</option>\n</select>",
        'com.atlassian.jira.plugin.system.customfieldtypes:multiselect': "{{ field.name }}{{ field.required ? '*' : '' }}:\n<select ui-select2 ng-model=\"value\" style=\"min-width: 200px;\" multiple ng-required=\"field.required\">\n  <option ng-repeat=\"val in field.allowedValues\" ng-value=\"val.id\">{{ field.schema.system ? val.name : val.value }}</option>\n</select>",
        'com.atlassian.jira.plugin.system.customfieldtypes:multicheckboxes': "{{ field.name }}{{ field.required ? '*' : '' }}:\n<label ng-repeat=\"val in field.allowedValues\" ng-init=\"$parent.value = []\">\n  <input type=\"checkbox\" ng-value=\"val.id\"\n      ng-checked=\"$parent.value.indexOf(val.id) > -1\"\n      ng-click=\"checkboxToggle(val.id)\"\n      />\n  {{ field.schema.system ? val.name : val.value }}\n</label>",
        'com.atlassian.jira.plugin.system.customfieldtypes:radiobuttons': "{{ field.name }}{{ field.required ? '*' : '' }}:\n<label ng-repeat=\"val in field.allowedValues\">\n  <input name=\"{{ field.id }}\" type=\"radio\" ng-value=\"val.id\" ng-model=\"$parent.value\" />\n  {{ field.schema.system ? val.name : val.value }}\n</label>",
        'com.atlassian.jira.plugin.system.customfieldtypes:labels': "{{ field.name }}{{ field.required ? '*' : '' }}:\n<input type=\"text\" ui-select2=\"{multiple: true, simple_tags: true, tags: []}\" ng-model=\"value\" ng-required=\"field.required\" />",
        'com.atlassian.jira.plugin.system.customfieldtypes:datepicker': "{{ field.name }}{{ field.required ? '*' : '' }}:\n<div class=\"dropdown\" style=\"display: inline-block\">\n  <a class=\"dropdown-toggle\" role=\"button\" data-toggle=\"dropdown\" data-target=\"#\" href=\"#\">\n    <div class=\"input-group\">\n      <input type=\"text\" class=\"form-control\" ng-model=\"value\" ng-required=\"field.required\">\n      <span class=\"input-group-addon\"><i class=\"fa fa-calendar\"></i></span>\n    </div>\n  </a>\n  <ul class=\"dropdown-menu\">\n    <datetimepicker ng-model=\"value\" data-datetimepicker-config=\"{minView: 'day'}\"/>\n  </ul>\n</div>",
        'com.atlassian.jira.plugin.system.customfieldtypes:datetime': "{{ field.name }}{{ field.required ? '*' : '' }}:\n<div class=\"dropdown\" style=\"display: inline-block\">\n  <a class=\"dropdown-toggle\" role=\"button\" data-toggle=\"dropdown\" data-target=\"#\" href=\"#\">\n    <div class=\"input-group\">\n      <input type=\"text\" class=\"form-control\" ng-model=\"value\" ng-required=\"field.required\">\n      <span class=\"input-group-addon\"><i class=\"fa fa-calendar\"></i></span>\n    </div>\n  </a>\n  <ul class=\"dropdown-menu\">\n    <datetimepicker ng-model=\"value\" data-datetimepicker-config=\"{minView: 'minute'}\"/>\n  </ul>\n</div>"
      };
      remap = {
        description: 'com.atlassian.jira.plugin.system.customfieldtypes:textarea',
        duedate: 'com.atlassian.jira.plugin.system.customfieldtypes:datepicker',
        labels: 'com.atlassian.jira.plugin.system.customfieldtypes:labels',
        priority: 'com.atlassian.jira.plugin.system.customfieldtypes:select',
        resolution: 'com.atlassian.jira.plugin.system.customfieldtypes:select',
        resolutiondate: 'com.atlassian.jira.plugin.system.customfieldtypes:datetime',
        created: 'com.atlassian.jira.plugin.system.customfieldtypes:datetime',
        updated: 'com.atlassian.jira.plugin.system.customfieldtypes:datetime'
      };
      return {
        restrict: 'AE',
        scope: {
          getField: '&field',
          model: '=ngModel',
          error: '='
        },
        link: function($scope, $el, $attr) {
          var $template, field, tpl;
          field = $scope.field = $scope.getField();
          if (!field.schema) {
            return;
          }
          tpl = field.schema.custom;
          if (!tpl) {
            tpl = field.schema.system;
          }
          tpl = remap[tpl] || tpl;
          if (!templates[tpl]) {
            tpl = 'com.atlassian.jira.plugin.system.customfieldtypes:textfield';
          }
          $template = $("<div class=\"input-group\" ng-class=\"error && 'text-danger'\" style=\"margin:10px auto;\">\n  " + templates[tpl] + "\n</div>");
          return $el.replaceWith($compile($template)($scope));
        },
        controller: function($scope) {
          var mapModel, types;
          types = {
            'com.atlassian.jira.plugin.system.customfieldtypes:multiselect': 'array_objects',
            'com.atlassian.jira.plugin.system.customfieldtypes:multicheckboxes': 'array_objects',
            'com.atlassian.jira.plugin.system.customfieldtypes:select': 'object',
            'com.atlassian.jira.plugin.system.customfieldtypes:radiobuttons': 'object',
            'com.atlassian.jira.plugin.system.customfieldtypes:float': 'float',
            'com.atlassian.jira.plugin.system.customfieldtypes:datetime': 'datetime',
            priority: 'object',
            resolution: 'object',
            parent: function(val) {
              return {
                key: val
              };
            }
          };
          mapModel = function(val) {
            var schema, type;
            if (val == null) {
              return $scope.model = val;
            }
            schema = $scope.getField().schema;
            type = schema.custom || schema.system;
            type = types[type];
            switch (type) {
              case 'array_objects':
                val = val.map(function(item) {
                  return {
                    id: item
                  };
                });
                break;
              case 'object':
                val = {
                  id: val
                };
                break;
              case 'float':
                val = parseFloat(val);
                if (NaN === val) {
                  val = null;
                }
                break;
              case 'datetime':
                if (moment && val instanceof Date) {
                  val = moment(val).format('YYYY-MM-DDTHH:mm:ss.SSSZZ');
                }
            }
            if ('function' === typeof type) {
              val = type(val);
            }
            return $scope.model = val;
          };
          $scope.$watch('value', mapModel);
          return $scope.checkboxToggle = function(val) {
            var idx;
            if (val == null) {
              return;
            }
            idx = $scope.value.indexOf(val);
            if (idx > -1) {
              $scope.value.splice(idx, 1);
            } else {
              $scope.value.push(val);
            }
            return mapModel($scope.value);
          };
        }
      };
    };
  });

}).call(this);

//# sourceMappingURL=JIRAFormWidget.js.map
