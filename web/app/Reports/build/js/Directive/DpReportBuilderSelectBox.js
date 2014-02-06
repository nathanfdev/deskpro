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
          template: "<span class=\"title-text\">\n	<span ng-repeat=\"text in texts\">\n		<span style=\"vertical-align:middle;\">{{ text }}</span>\n		<select ng-model=\"selected[$index]\" ui-select2 style=\"min-width:70px;\">\n			<option ng-repeat=\"option in options[$index]\" ng-value=\"option.value\" ng-selected=\"selected[$parent.$index] == option.value\">\n				{{ option.label }}\n			</option>\n		</select>\n	</span>\n</span>\n",
          link: function(scope, element, attrs) {
            /*
            
             			Below vairbales will look liek following
            
             			scope.texts = ['Number of tickets created','grouped by',' & ']
            				scope.options = [[{value: 'yesterday', label: 'Yesterday'}, {value: 'today', label: 'Today'}, {value: '123', label: '123'}, {value: '456', label: '456'}]
            																					[{value: 'department', label: 'Department'}, {value: 'agent', label: 'Agent'}]
            																					[{value: 'department', label: 'Department'}, {value: 'agent', label: 'Agent'}]
            				]
            				scope.selected = ['today', 'agent', 'department']
            */

            var buildDirectiveVariables, collectSelectOptions,
              _this = this;
            scope.texts = [];
            scope.options = [];
            scope.selected = [];
            scope.$watch(attrs.possibleValues, function(newVal, oldVal) {
              var valueToDecorate;
              if (typeof newVal === 'undefined') {
                return;
              }
              valueToDecorate = scope.$eval(attrs.valueToDecorate);
              if (!valueToDecorate) {
                return;
              }
              return buildDirectiveVariables(valueToDecorate);
            });
            /*
            				# This function builds directive by constructing it on 'the fly' using DOM operations
             			# The reason for doing so - problems with inner directives that were compiled with $compile() functionality
            */

            buildDirectiveVariables = function(value) {
              var collected, match, regex, _results;
              regex = /([\w\s\&,]*)(<(\d+:.+?)>)/g;
              _results = [];
              while (match = regex.exec(value)) {
                scope.texts.push(match[1]);
                collected = collectSelectOptions(match[3]);
                scope.options.push(collected.options);
                _results.push(scope.selected.push(collected.selected));
              }
              return _results;
            };
            /*
            				# Returning select box options that was rendered according to 'input' parameter
            */

            collectSelectOptions = function(input) {
              var choices, extras, extrasMatch, key, match, options, possibleValues, regex, type, value;
              possibleValues = scope.$eval(attrs.possibleValues);
              choices = {};
              extras = {};
              options = [];
              if (input.match(/^\d+:date group(.*)$/)) {
                choices = possibleValues.dates;
                extrasMatch = RegExp.$1;
              } else if (input.match(/^\d+:field group:([a-zA-Z0-9_]+)(.*)$/)) {
                type = RegExp.$1;
                if (typeof possibleValues.fields[type] !== 'undefined') {
                  choices = possibleValues.fields[type];
                  extrasMatch = RegExp.$2;
                }
              } else if (input.match(/^\d+:status group:([a-zA-Z0-9_]+)(.*)$/)) {
                type = RegExp.$1;
                if (typeof possibleValues.statuses[type] !== 'undefined') {
                  choices = possibleValues.statuses[type];
                  extrasMatch = RegExp.$2;
                }
              } else if (input.match(/^\d+:order group:([a-zA-Z0-9_]+)(.*)$/)) {
                type = RegExp.$1;
                if (typeof possibleValues.orders[type] !== 'undefined') {
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
                value = choices[key];
                options.push({
                  value: key,
                  label: value[0]
                });
              }
              return {
                options: options,
                selected: (extras["default"] ? extras["default"] : options[0].value)
              };
            };
            /*
             			#
            */

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