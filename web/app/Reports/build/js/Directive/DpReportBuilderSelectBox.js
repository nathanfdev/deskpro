(function() {
  var __hasProp = {}.hasOwnProperty;

  define(function() {
    /*
      # Description
      # -----------
    		#
    		#	Example View
    		#	------------
    		#	<dp-report-builder-select-box
    		#				  value-to-decorate="scope.title"
    		#				  possible-values="scope.some_object">
    		#	</span>
    		#
    		#	Parameters
    		#	------------
    		# 1) 'value-to-decorate' (required parameter) - ...
    		# 2) 'possible-values' (required parameter) - ...
      #
    */

    var Reports_Directive_DpReportBuilderSelectBox;
    Reports_Directive_DpReportBuilderSelectBox = [
      '$compile', function($compile) {
        return {
          restrict: 'AE',
          scope: {},
          template: "<span class=\"title-text\">\n	<span ng-repeat=\"text in texts\">\n		<span style=\"vertical-align:middle;\">{{ text }}</span>\n		<select ng-model=\"selected[$index]\" ui-select2>\n			<option ng-repeat=\"option in options[$index]\" ng-value=\"option.value\" ng-selected=\"selected[$parent.$index] == option.value\">\n				{{ option.label }}\n			</option>\n		</select>\n	</span>\n</span>\n",
          link: function(scope, element, attrs) {
            var buildDirectiveContent, _returnSelectBoxElement,
              _this = this;
            scope.texts = ['Number of tickets created', 'grouped by', ' & '];
            scope.options = [
              [
                {
                  value: 'yesterday',
                  label: 'Yesterday'
                }, {
                  value: 'today',
                  label: 'Today'
                }, {
                  value: '123',
                  label: '123'
                }, {
                  value: '456',
                  label: '456'
                }
              ], [
                {
                  value: 'department',
                  label: 'Department'
                }, {
                  value: 'agent',
                  label: 'Agent'
                }
              ], [
                {
                  value: 'department',
                  label: 'Department'
                }, {
                  value: 'agent',
                  label: 'Agent'
                }
              ]
            ];
            scope.selected = ['today', 'agent', 'department'];
            scope.$watch(attrs.possibleValues, function(newVal, oldVal) {
              var valueToDecorate;
              if (typeof newVal === 'undefined') {
                return;
              }
              valueToDecorate = scope.$eval(attrs.valueToDecorate);
              if (!valueToDecorate) {
                return;
              }
              return buildDirectiveContent(valueToDecorate);
            });
            /*
            				# This function builds directive by constructing it on 'the fly' using DOM operations
             			# The reason for doing so - problems with inner directives that were compiled with $compile() functionality
            */

            buildDirectiveContent = function(value) {
              var newValue, regex;
              newValue = value;
              return regex = /<(\d+:.+?)>/g;
            };
            _returnSelectBoxElement = function(value) {
              var choice_value, choices, extras, extrasMatch, key, match, options, possibleValues, regex, selectElement, type;
              possibleValues = scope.$eval(attrs.possibleValues);
              choices = {};
              extras = {};
              options = [];
              if (value.match(/^\d+:date group(.*)$/)) {
                choices = possibleValues.dates;
                extrasMatch = RegExp.$1;
              } else if (value.match(/^\d+:field group:([a-zA-Z0-9_]+)(.*)$/)) {
                type = RegExp.$1;
                if (typeof possibleValues.fields[type] !== 'undefined') {
                  choices = possibleValues.fields[type];
                  extrasMatch = RegExp.$2;
                }
              } else if (value.match(/^\d+:status group:([a-zA-Z0-9_]+)(.*)$/)) {
                type = RegExp.$1;
                if (typeof possibleValues[type] !== 'undefined') {
                  choices = possibleValues.statuses[type];
                  extrasMatch = RegExp.$2;
                }
              } else if (value.match(/^\d+:order group:([a-zA-Z0-9_]+)(.*)$/)) {
                type = RegExp.$1;
                if (typeof possibleValues[type] !== 'undefined') {
                  choices = possibleValues.orders[type];
                  extrasMatch = RegExp.$2;
                }
              }
              if (extrasMatch) {
                regex = /,([a-zA-Z0-9_ ]+):([^,]+)/g;
                while (match = regex.exec(extrasMatch)) {
                  extras[$.trim(match[1])] = $.trim(match[2]);
                }
              }
              for (key in choices) {
                if (!__hasProp.call(choices, key)) continue;
                choice_value = choices[key];
                options.push({
                  value: key,
                  label: choice_value[0]
                });
              }
              selectElement = angular.element("<select ng-change=\"selectHandler()\"\n	ng-model=\"selectedOption\"\n	ng-options=\"opt as opt.label for opt in options\">\n</select>");
              $compile(selectElement)(scope);
              return selectElement;
            };
            return scope.selectHandler = function() {
              return alert('ok');
            };
          }
        };
      }
    ];
    return Reports_Directive_DpReportBuilderSelectBox;
  });

}).call(this);

/*
//@ sourceMappingURL=DpReportBuilderSelectBox.js.map
*/