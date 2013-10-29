(function() {
  var __hasProp = {}.hasOwnProperty;

  define(function() {
    /*
    	# The dpOptionBuilder directive is a component that handles a form that adds/removes rows
    	# (e.g., a search builder, an option builder etc)
    	#
    	# The dpOptionBuilder is made up of a few parts:
       #
    	# - The options select box which defines the available options to choose from
       #
    	# - Type definitions (the typesDef attribute) must be an object that
    	# knows how to create the available options. It knows the correct template,
    	# knows how to fetch the required data (e.g., options for a select box) and
    	# optionally can specify methods to convert model data to and from the stored value.
       #
       # typesDef must have a getDef method that returns an object with these methods:
       # - getTemplate: Returns either a string template or a promise if the template is loaded somewhere else
       # - getData: Returns an object or a promise
       # - getDataFormatter: Optional. An object with getViewValue() to convert the view data into
       #                     a saveable model, and getValue() to convert a saved model to the view.
       #
       # Example
       # -------
       #
       # view.html:
       #
    	#    <dp-option-builder types-def="myTypeDef" ng-model="trigger_criteria">
    	#    	<select>
    	#    		<option value="0">Add criteria</option>
    	#    		<option value="my_row">Example</option>
    	#    	</select>
    	#    </dp-option-builder>
       #
       # myTypeDef.coffee:
    	#     {
    	#         getDef: (type) ->
    	#         	return {
    	#     			getTemplate: ->
    	#     				return "<dp-optionbuilder-row>{{ title }} <input type="text" ng-model="model.value" /></dp-optionbuilder-row>"
    	#
    	#     			getData: ->
    	#         			return { title: "My Input" }
    	#
    	#         		getDataFormatter: {
    	#         			getViewValue: (value) ->
       #    					return { value: value.my_value }
       #     				getValue: (view_model) ->
       #     					return { my_value: view_model.value }
    	#         		}
    	#         	}
    	#     }
    	#
    */

    var DeskPRO_OptionBuilder_Controller;
    return DeskPRO_OptionBuilder_Controller = (function() {
      function DeskPRO_OptionBuilder_Controller($scope, $element, $attrs, $transclude, dpTemplateManager, $compile, $q) {
        var _this = this;
        this.dpTemplateManager = dpTemplateManager;
        this.$scope = $scope;
        this.$compile = $compile;
        this.element = $element;
        this.attrs = $attrs;
        this.rows = {};
        this.rowsCount = 0;
        this.$q = $q;
        this.typesDef = this.$scope.getTypesDef();
        this.options = this.$scope.getOptions() || {};
        this.addBtnText = 'Add';
        this.els = {};
        $transclude(function(clone) {
          var addBtnLabel, select;
          select = $('<select/>').css('width', '100%');
          addBtnLabel = clone.filter('add-btn-label');
          if (addBtnLabel[0]) {
            _this.addBtnText = addBtnLabel.text();
          }
          return _this.element.find('.select2-wrap').append(select);
        });
        this.els.select = this.element.find('.select2-wrap').find('select').first();
        this.updateOptionTypes();
        this.els.select.select2();
        this.els.select.on('change', function() {
          _this.els.addBtn.click();
          return _this.els.select.select2('val', '0');
        });
        this.els.optionList = this.element.find('.dp-ob-options');
        this.els.noOptionsMessage = this.els.optionList.find('.dp-ob-no-options');
        this.els.loadingOptionMessage = this.els.optionList.find('.dp-ob-loading-options');
        this.els.addBtn = this.element.find('.add_btn');
        this.els.addBtn.on('click', function(ev) {
          var selected_opt;
          ev.preventDefault();
          selected_opt = _this.els.select.find(':selected').first();
          if (!selected_opt[0] || selected_opt.val() === "0") {
            _this.els.select.select2('open');
            return;
          }
          return _this.addRow(selected_opt.val(), {});
        });
        this.$scope.$watchCollection('optionTypes', function() {
          return _this.updateOptionTypes();
        });
      }

      /*
        	# Updates the option types available in the select box
      */


      DeskPRO_OptionBuilder_Controller.prototype.updateOptionTypes = function() {
        var item, opt, optgroup, subItem, _i, _j, _len, _len1, _ref, _ref1, _results;
        this.els.select.empty();
        $('<option/>').val('0').text(this.addBtnText).appendTo(this.els.select);
        _ref = this.$scope.optionTypes;
        _results = [];
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          item = _ref[_i];
          if (item.subOptions != null) {
            optgroup = $('<optgroup/>').attr('label', item.title);
            _ref1 = item.subOptions;
            for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
              subItem = _ref1[_j];
              opt = $('<option/>').val(subItem.value).text(subItem.title);
              optgroup.append(opt);
            }
            _results.push(this.els.select.append(optgroup));
          } else {
            opt = $('<option/>').val(item.value).text(item.title);
            _results.push(this.els.select.append(opt));
          }
        }
        return _results;
      };

      /*
        	# Add a new row to the form
        	#
        	# @param {String} type
        	# @param {Object} value
      */


      DeskPRO_OptionBuilder_Controller.prototype.addRow = function(type, value) {
        var dataFormatter, dataPromise, def, retData, retTpl, tplPromise,
          _this = this;
        def = this.typesDef.getDef(type);
        tplPromise = def.getTemplate();
        dataPromise = def.getData();
        dataFormatter = def.getDataFormatter();
        if (!this.$q.isPromise(tplPromise)) {
          retTpl = tplPromise;
          tplPromise = this.$q.fcall(function() {
            return retTpl;
          });
        }
        if (!this.$q.isPromise(dataPromise)) {
          retData = dataPromise;
          dataPromise = this.$q.fcall(function() {
            return retData;
          });
        }
        this.els.loadingOptionMessage.show();
        return this.$q.all([tplPromise, dataPromise]).then(function(returns) {
          var data, element, k, option_row, option_title, rowScope, tpl, v;
          tpl = returns[0];
          data = returns[1];
          option_row = _this.els.select.find('option[value="' + type + '"]').first();
          if (option_row[0]) {
            option_title = option_row.text();
          }
          rowScope = _this.$scope.$new();
          rowScope.type = type;
          rowScope.type_title = option_title;
          if (dataFormatter) {
            rowScope.value = value || {};
            rowScope.model = dataFormatter.getViewValue(rowScope.value, data);
            rowScope.$watch(rowScope.model, function(newModel) {
              return rowScope.value = dataFormatter.getValue(newModel, data);
            });
          } else {
            rowScope.model = {};
            rowScope.value = rowScope.model;
          }
          if (data) {
            for (k in data) {
              if (!__hasProp.call(data, k)) continue;
              v = data[k];
              rowScope[k] = v;
            }
          }
          if (_this.options.tag_first && !_this.rowsCount) {
            rowScope.tag = _this.options.tag_first;
          }
          if (_this.options.tag_after && _this.rowsCount) {
            rowScope.tag = _this.options.tag_after;
          }
          element = _this.$compile(tpl)(rowScope);
          element.find('.remove-row-trigger').on('click', function(ev) {
            ev.preventDefault();
            return _this.removeRow(element);
          });
          element.data('scopeId', rowScope.$id);
          _this.els.loadingOptionMessage.hide();
          _this.els.noOptionsMessage.hide();
          _this.els.optionList.append(element);
          _this.rowsCount++;
          return _this.rows[rowScope.$id] = {
            element: element,
            scope: rowScope
          };
        });
      };

      /*
        	# Removes a row by element
        	#
        	# @param {HTMLElememnt} row
      */


      DeskPRO_OptionBuilder_Controller.prototype.removeRow = function(row) {
        var scopeId;
        scopeId = $(row).data('scopeId');
        return this.removeRowById(scopeId);
      };

      /*
        	# Removes a row by a scope ID
        	#
        	# @param {Integer} scopeId
      */


      DeskPRO_OptionBuilder_Controller.prototype.removeRowById = function(scopeId) {
        var row;
        row = this.rows[scopeId];
        delete this.rows[scopeId];
        row.element.remove();
        row.scope.$destroy();
        this.rowsCount--;
        if (this.rowsCount === 0) {
          this.els.noOptionsMessage.show();
        }
        return true;
      };

      /*
        	# Collect all data from all rows in the builder
        	#
        	# @return {Array}
      */


      DeskPRO_OptionBuilder_Controller.prototype.collectData = function() {
        var data, r, row, scopeId, _ref;
        data = [];
        _ref = this.rows;
        for (scopeId in _ref) {
          if (!__hasProp.call(_ref, scopeId)) continue;
          row = _ref[scopeId];
          r = row.scope.value;
          if (r) {
            data.push(r);
          }
        }
        return data;
      };

      DeskPRO_OptionBuilder_Controller.FACTORY = [
        '$scope', '$element', '$attrs', '$transclude', 'dpTemplateManager', '$compile', '$q', function($scope, $element, $attrs, $transclude, dpTemplateManager, $compile, $q) {
          return new DeskPRO_OptionBuilder_Controller($scope, $element, $attrs, $transclude, dpTemplateManager, $compile, $q);
        }
      ];

      return DeskPRO_OptionBuilder_Controller;

    })();
  });

}).call(this);

/*
//@ sourceMappingURL=Controller.js.map
*/