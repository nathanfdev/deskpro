(function() {
  var __hasProp = {}.hasOwnProperty;

  define(function() {

    /*
    	 * Description
    	 * -----------
    	 *
    	 *	Example View
    	 *	------------
    	 *	<dp-report-builder-title>
    	 *	</dp-report-builder-title>
    	 *
    	 *	Parameters
    	 *	------------
    	 *
     */
    var Reports_Directive_DpReportTitle;
    Reports_Directive_DpReportTitle = [
      '$state', '$location', function($state, $location) {
        return {
          restrict: 'AE',
          replace: true,
          scope: {
            possibleValues: '=',
            valueToDecorate: '=',
            reportId: '='
          },
          template: "<h3 style=\"font-weight:bold;\">\n	<span ng-repeat=\"text in texts\" class=\"inline-select2\">\n		<span style=\"vertical-align:middle;\" ng-bind-html=\"text\"></span>\n		<select ng-if=\"options[$index]\" ng-model=\"selected[$index]\" ui-select2=\"{dropdownAutoWidth:true}\" style=\"min-width:100px;\" ng-change=\"changeLinkParams()\">\n			<option ng-repeat=\"option in options[$index]\" ng-value=\"option.value\" ng-selected=\"selected[$parent.$index] == option.value\">\n				{{ option.label }}\n			</option>\n		</select>\n	</span>\n</h3>",
          link: function(scope, element, attrs) {

            /*
            
            				Below variables will look like following
            
            				scope.texts = ['Number of tickets created','grouped by',' & ']
            				scope.options = [[{value: 'yesterday', label: 'Yesterday'}, {value: 'today', label: 'Today'}, {value: '123', label: '123'}, {value: '456', label: '456'}]
            																					[{value: 'department', label: 'Department'}, {value: 'agent', label: 'Agent'}]
            																					[{value: 'department', label: 'Department'}, {value: 'agent', label: 'Agent'}]
            				]
            				scope.selected = ['today', 'agent', 'department']
             */
            var buildDirectiveVariables, collectSelectOptions;
            scope.texts = [];
            scope.options = [];
            scope.selected = [];
            scope.defaultLinkParams = '';
            scope.type = attrs.type || 'builtIn';
            scope.$watch('possibleValues', (function(_this) {
              return function(newVal) {
                var valueToDecorate;
                if (typeof newVal === 'undefined') {
                  return;
                }
                valueToDecorate = scope.valueToDecorate;
                if (!valueToDecorate) {
                  return;
                }
                buildDirectiveVariables(valueToDecorate);
                return scope.defaultLinkParams = scope.selected.join(',');
              };
            })(this));

            /*
            				 * This function builds directive by constructing it on 'the fly' using DOM operations
            				 * The reason for doing so - problems with inner directives that were compiled with $compile() functionality
             */
            buildDirectiveVariables = function(value) {
              var collected, key, lastPiece, match, params, regex;
              key = 0;
              params = $state.params.params.split(',');
              lastPiece = value;
              regex = /([\w\s\&,]*)(<(\d+:.+?)>)/g;
              while (match = regex.exec(value)) {
                scope.texts.push(match[1]);
                collected = collectSelectOptions(match[3]);
                scope.options.push(collected.options);
                scope.selected.push(params[key]);
                key++;
                lastPiece = lastPiece.replace(match[1], '').replace(match[2], '');
              }
              lastPiece = lastPiece.replace('[', '').replace(']', '');
              lastPiece = lastPiece.replace(/<chart:([a-z0-9_-]+)>/gi, '<span class="report-chart-icon report-chart-icon-$1"></span>');
              return scope.texts.push(lastPiece);
            };

            /*
            				 * Returning select box options that was rendered according to 'input' parameter
             */
            collectSelectOptions = function(input) {
              var choices, extras, extrasMatch, key, options, possibleValues, type, value;
              possibleValues = scope.possibleValues;
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
              for (key in choices) {
                if (!__hasProp.call(choices, key)) continue;
                value = choices[key];
                options.push({
                  value: key,
                  label: value[0]
                });
              }
              return {
                options: options
              };
            };

            /*
            				 * Going to correponding route after changing selected options inside select box
             */
            return scope.changeLinkParams = function() {
              var href, linkParams;
              linkParams = scope.selected.join(',');
              href = $state.href('builder.edit', {
                id: scope.reportId,
                params: linkParams,
                type: scope.type
              });
              return window.location.hash = href;
            };
          }
        };
      }
    ];
    return Reports_Directive_DpReportTitle;
  });

}).call(this);

//# sourceMappingURL=DpReportBuilderTitle.js.map
