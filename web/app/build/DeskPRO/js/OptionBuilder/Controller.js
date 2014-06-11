(function() {
  var __hasProp = {}.hasOwnProperty;

  define(['DeskPRO/Util/Util'], function(Util) {

    /*
    	 * The dpOptionBuilder directive is a component that handles a form that adds/removes rows
    	 * (e.g., a search builder, an option builder etc)
    	 *
    	 * The dpOptionBuilder is made up of a few parts:
        *
    	 * - The options select box which defines the available options to choose from
        *
    	 * - Type definitions (the typesDef attribute) must be an object that
    	 * knows how to create the available options. It knows the correct template,
    	 * knows how to fetch the required data (e.g., options for a select box) and
    	 * optionally can specify methods to convert model data to and from the stored value.
        *
        * typesDef must have a getDef method that returns an object with these methods:
        * - getTemplate: Returns either a string template or a promise if the template is loaded somewhere else
        * - getData: Returns an object or a promise
        * - getDataFormatter: Optional. An object with getViewValue() to convert the view data into
        *                     a saveable model, and getValue() to convert a saved model to the view.
        *
        * Example
        * -------
        *
        * view.html:
        *
    	 *    <dp-option-builder types-def="myTypeDef" ng-model="trigger_criteria">
    	 *    	<select>
    	 *    		<option value="0">Add criteria</option>
    	 *    		<option value="my_row">Example</option>
    	 *    	</select>
    	 *    </dp-option-builder>
        *
        * myTypeDef.coffee:
    	 *     {
    	 *         getDef: (type) ->
    	 *         	return {
    	 *     			getTemplate: ->
    	 *     				return "<dp-optionbuilder-row>{{ title }} <input type="text" ng-model="model.value" /></dp-optionbuilder-row>"
    	 *
    	 *     			getData: ->
    	 *         			return { title: "My Input" }
    	 *
    	 *         		getDataFormatter: {
    	 *         			getViewValue: (value) ->
        *    					return { value: value.my_value }
        *     				getValue: (view_model) ->
        *     					return { my_value: view_model.value }
    	 *         		}
    	 *         	}
    	 *     }
    	 *
     */
    var DeskPRO_OptionBuilder_Controller;
    return DeskPRO_OptionBuilder_Controller = (function() {
      function DeskPRO_OptionBuilder_Controller($scope, $element, $attrs, $transclude, dpTemplateManager, $compile, $q, $injector, $timeout) {
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
        this.$injector = $injector;
        this.$timeout = $timeout;
        this.currentAddPromise = null;
        this.resetTime = (new Date()).getTime();
        this.els = {};
        $transclude((function(_this) {
          return function(clone) {
            var addBtnLabel, addBtnText, select;
            select = $('<select/>').css('width', '100%');
            addBtnLabel = clone.filter('add-btn-label');
            addBtnText = 'Add';
            if (addBtnLabel[0]) {
              addBtnText = addBtnLabel.text();
            }
            _this.element.find('.select2-wrap').append(select);
            return _this.element.find('.add_btn').find('label').text(addBtnText);
          };
        })(this));
        this.els.optionList = this.element.find('.dp-ob-options');
        this.els.noOptionsMessage = this.els.optionList.find('.dp-ob-no-options');
        this.els.loadingOptionMessage = this.els.optionList.find('.dp-ob-loading-options');
        if (this.typesDef.loadDataOptions != null) {
          this.typesDef.loadDataOptions().then((function(_this) {
            return function() {
              _this.hasLoaded = true;
              return _this.initControl();
            };
          })(this));
        } else {
          this.hasLoaded = true;
          this.initControl();
        }
      }

      DeskPRO_OptionBuilder_Controller.prototype.initControl = function() {
        this.els.select = this.element.find('.select2-wrap').find('select').first();
        this.els.select.select2({
          dropdownCssClass: 'dp-ob-select2'
        });
        this.els.select.on('change', (function(_this) {
          return function() {
            var selected_opt;
            selected_opt = _this.els.select.find(':selected').first();
            _this.addRow(selected_opt.val(), {});
            return _this.els.select.select2('val', '0');
          };
        })(this));
        this.updateOptionTypes();
        this.$scope.$watchCollection('optionTypes', (function(_this) {
          return function() {
            return _this.updateOptionTypes();
          };
        })(this));
        return this.$scope.$watch('saveTarget', (function(_this) {
          return function() {
            return _this.reset();
          };
        })(this));
      };

      DeskPRO_OptionBuilder_Controller.prototype.reset = function() {
        var alreadyDone, exist, existId, f, id, row, rowId, term, _i, _len, _ref, _ref1, _ref2, _ref3, _ref4, _results;
        this.resetTime = (new Date()).getTime();
        if (!this.hasLoaded) {
          return;
        }
        this.rowsCount = 0;
        _ref = this.rows;
        for (id in _ref) {
          row = _ref[id];
          row.element.remove();
          row.scope.$destroy();
          delete this.rows[id];
        }
        this.els.noOptionsMessage.show();
        alreadyDone = {};
        if (this.options.fixedExpanded) {
          _ref1 = this.options.fixedExpanded;
          for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
            f = _ref1[_i];
            exist = null;
            existId = null;
            _ref2 = this.$scope.saveTarget;
            for (rowId in _ref2) {
              if (!__hasProp.call(_ref2, rowId)) continue;
              term = _ref2[rowId];
              if (term && term.type && term.type === f) {
                exist = term;
                existId = rowId;
                alreadyDone[rowId] = true;
                break;
              }
            }
            this.addRow(f, exist, existId, true, ((_ref3 = this.options.fixOn) != null ? _ref3.indexOf(f) : void 0) !== -1);
          }
        }
        if (this.$scope.saveTarget) {
          _ref4 = this.$scope.saveTarget;
          _results = [];
          for (rowId in _ref4) {
            if (!__hasProp.call(_ref4, rowId)) continue;
            term = _ref4[rowId];
            if (alreadyDone[rowId]) {
              continue;
            }
            if (term && term.type) {
              _results.push(this.addRow(term.type, term, rowId));
            } else {
              _results.push(void 0);
            }
          }
          return _results;
        }
      };


      /*
        	 * Updates the option types available in the select box
       */

      DeskPRO_OptionBuilder_Controller.prototype.updateOptionTypes = function() {
        var item, opt, optgroup, subItem, _i, _j, _len, _len1, _ref, _ref1, _results;
        this.els.select.empty();
        this.els.select.append($('<option value="0" />'));
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
        	 * Add a new row to the form
        	 *
        	 * @param {String} type
        	 * @param {Object} value
       */

      DeskPRO_OptionBuilder_Controller.prototype.addRow = function(type, value, existId, isFixed, isFixedOn) {
        var dataFormatter, dataPromise, def, placeholder, retData, retTpl, run, scopeInit, time, tplPromise;
        if (!this.hasLoaded) {
          return;
        }
        def = this.typesDef.getDef(type);
        tplPromise = def.getTemplate();
        dataPromise = def.getData();
        dataFormatter = def.getDataFormatter();
        scopeInit = def.scopeInit != null ? def.scopeInit : null;
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
        placeholder = $('<div/>');
        this.els.optionList.append(placeholder);
        time = (new Date()).getTime();
        this.els.loadingOptionMessage.show().addClass('loading-on');
        run = (function(_this) {
          return function(tpl, data, isRetry) {
            var element, k, option_title, rowId, rowScope, sb, v, _i, _j, _len, _len1, _ref, _ref1;
            if (time < _this.resetTime) {
              return;
            }
            option_title = null;
            _ref = _this.$scope.optionTypes;
            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
              v = _ref[_i];
              if (v.subOptions) {
                _ref1 = v.subOptions;
                for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
                  sb = _ref1[_j];
                  if (type === sb.value) {
                    option_title = sb.title;
                    break;
                  }
                }
              } else {
                if (type === v.value) {
                  option_title = v.title;
                }
              }
              if (option_title) {
                break;
              }
            }
            if (!option_title && (!isRetry || isRetry < 20)) {
              _this.$timeout(function() {
                return run(tpl, data, isRetry ? 1 : isRetry + 1);
              }, 140);
              return;
            }
            rowScope = _this.$scope.$new();
            rowScope.type = type;
            rowScope.type_title = option_title;
            if (existId) {
              rowId = existId;
            } else {
              rowId = rowScope.$id;
            }
            if (dataFormatter) {
              rowScope.value = value || {};
              rowScope.model = dataFormatter.getViewValue(rowScope.value, data);
              rowScope.$watch('model', function() {
                rowScope.value = dataFormatter.getValue(rowScope.model, data);
                return _this.$scope.saveTarget[rowId] = rowScope.value;
              }, true);
            } else {
              rowScope.model = {};
              rowScope.value = rowScope.model;
              rowScope.$watch('model', function() {
                rowScope.value = rowScope.model;
                return _this.$scope.saveTarget[rowId] = rowScope.value;
              }, true);
            }
            if (data) {
              for (k in data) {
                if (!__hasProp.call(data, k)) continue;
                v = data[k];
                rowScope[k] = v;
              }
            }
            if (scopeInit) {
              _this.$injector.invoke(scopeInit, _this, {
                '$scope': rowScope
              });
            }
            rowScope.rowOpts = {};
            rowScope.rowOpts.rowEnabled = true;
            if (isFixed) {
              if (existId || isFixedOn) {
                rowScope.rowOpts.rowEnabled = true;
              } else {
                rowScope.rowOpts.rowEnabled = false;
              }
              rowScope.rowOpts.hideRemove = true;
              rowScope.rowOpts.isFixedOn = isFixedOn;
              rowScope.rowOpts.withCheck = true;
              rowScope.rowOpts.withCheckId = Util.uid('check');
            }
            rowScope.rowOpts.rowIdx = _this.rowsCount + 1;
            rowScope.rowOpts.tagString = _this.options.tagString || null;
            rowScope.rowOpts.tagClass = _this.options.tagClass || '';
            element = _this.$compile(tpl)(rowScope);
            rowScope.rowFn = {};
            rowScope.rowFn.removeRow = function() {
              return _this.removeRow(element);
            };
            if (rowScope.rowOpts.withCheckId) {
              element.find('.row-label').attr('for', rowScope.rowOpts.withCheckId);
            }
            rowScope.$emit('rowAdded', _this, element, rowScope);
            element.data('scopeId', rowId);
            _this.els.loadingOptionMessage.hide().removeClass('loading-on');
            _this.els.noOptionsMessage.hide();
            placeholder.replaceWith(element);
            _this.rowsCount++;
            _this.rows[rowId] = {
              element: element,
              scope: rowScope
            };
            _this.$scope.saveTarget[rowId] = rowScope.value;
            if (isFixed && !isFixedOn) {
              if (rowScope.rowOpts.rowEnabled) {
                _this.$scope.saveTarget[rowId] = rowScope.value;
              } else {
                _this.$scope.saveTarget[rowId] = {
                  DP_DISABLED: true
                };
              }
              return rowScope.$watch('rowOpts.rowEnabled', function(rowEnabled) {
                if (rowEnabled) {
                  return delete rowScope.value.DP_DISABLED;
                } else {
                  return rowScope.value.DP_DISABLED = true;
                }
              });
            }
          };
        })(this);
        return this.$q.all([tplPromise, dataPromise]).then((function(_this) {
          return function(returns) {
            var data, tpl;
            tpl = returns[0];
            data = returns[1];
            return run(tpl, data);
          };
        })(this));
      };


      /*
        	 * Removes a row by element
        	 *
        	 * @param {HTMLElememnt} row
       */

      DeskPRO_OptionBuilder_Controller.prototype.removeRow = function(row) {
        var scopeId;
        scopeId = $(row).data('scopeId');
        return this.removeRowById(scopeId);
      };


      /*
        	 * Removes a row by a scope ID
        	 *
        	 * @param {Integer} scopeId
       */

      DeskPRO_OptionBuilder_Controller.prototype.removeRowById = function(scopeId) {
        var idx, row, _ref;
        row = this.rows[scopeId];
        delete this.rows[scopeId];
        delete this.$scope.saveTarget[scopeId];
        row.scope.$emit('rowRemoved', this, row.element, row.scope, this.rowsCount - 1);
        row.element.remove();
        row.scope.$destroy();
        this.rowsCount--;
        if (this.rowsCount === 0) {
          this.els.noOptionsMessage.show();
        } else {
          idx = 0;
          _ref = this.rows;
          for (row in _ref) {
            if (!__hasProp.call(_ref, row)) continue;
            row.scope.rowOpts.rowIdx = idx;
            idx++;
          }
        }
        return true;
      };

      DeskPRO_OptionBuilder_Controller.FACTORY = [
        '$scope', '$element', '$attrs', '$transclude', 'dpTemplateManager', '$compile', '$q', '$injector', '$timeout', function($scope, $element, $attrs, $transclude, dpTemplateManager, $compile, $q, $injector, $timeout) {
          return new DeskPRO_OptionBuilder_Controller($scope, $element, $attrs, $transclude, dpTemplateManager, $compile, $q, $injector, $timeout);
        }
      ];

      return DeskPRO_OptionBuilder_Controller;

    })();
  });

}).call(this);

//# sourceMappingURL=Controller.js.map
