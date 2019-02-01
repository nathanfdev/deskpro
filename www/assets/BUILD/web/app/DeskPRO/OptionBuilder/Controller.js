define(['DeskPRO/Util/Util'], (Util) => {
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
  *     <select>
  *       <option value="0">Add criteria</option>
  *       <option value="my_row">Example</option>
  *     </select>
  *    </dp-option-builder>
    *
    * myTypeDef.coffee:
  *     {
  *         getDef: (type) ->
  *           return {
  *           getTemplate: ->
  *             return "<dp-optionbuilder-row>{{ title }} <input type="text" ng-model="model.value" /></dp-optionbuilder-row>"
  *
  *           getData: ->
  *               return { title: "My Input" }
  *
  *             getDataFormatter: {
  *               getViewValue: (value) ->
    *             return { value: value.my_value }
    *             getValue: (view_model) ->
    *               return { my_value: view_model.value }
  *             }
  *           }
  *     }
  *
  */
  class DeskPRO_OptionBuilder_Controller {
    static initClass() {
      this.FACTORY = ['$scope', '$element', '$attrs', '$transclude', 'dpTemplateManager', '$compile', '$q', '$injector', '$timeout', ($scope, $element, $attrs, $transclude, dpTemplateManager, $compile, $q, $injector, $timeout) => new DeskPRO_OptionBuilder_Controller($scope, $element, $attrs, $transclude, dpTemplateManager, $compile, $q, $injector, $timeout)
      ];
    }
    constructor($scope, $element, $attrs, $transclude, dpTemplateManager, $compile, $q, $injector, $timeout) {
      this.dpTemplateManager = dpTemplateManager;
      this.$scope            = $scope;
      this.$compile          = $compile;
      this.element           = $element;
      this.attrs             = $attrs;
      this.rows              = {};
      this.rowsCount         = 0;
      this.$q                = $q;
      this.typesDef          = this.$scope.getTypesDef();
      this.options           = this.$scope.getOptions() || {};
      this.$injector         = $injector;
      this.$timeout          = $timeout;
      this.currentAddPromise = null;
      this.resetTime         = (new Date()).getTime();

      this.els = {};

      $transclude((clone) => {
        const select = $('<select/>').css('width', '100%');

        const addBtnLabel = clone.filter('add-btn-label');
        let addBtnText = 'Add';
        if (addBtnLabel[0]) {
          addBtnText = addBtnLabel.text();
        }

        this.element.find('.select2-wrap').append(select);
        return this.element.find('.add_btn').find('label').text(addBtnText);
      });

      // The list to append options to
      this.els.optionList = this.element.find('.dp-ob-options');

      // The no options message
      this.els.noOptionsMessage = this.els.optionList.find('.dp-ob-no-options');

      // The loading message
      this.els.loadingOptionMessage = this.els.optionList.find('.dp-ob-loading-options');

      if (this.typesDef.loadDataOptions != null) {
        this.typesDef.loadDataOptions().then(() => {
          this.hasLoaded = true;
          return this.initControl();
        });
      } else {
        this.hasLoaded = true;
        this.initControl();
      }
    }


    initControl() {
      // Select box
      this.els.select = this.element.find('.select2-wrap').find('select').first();
      this.els.select.select2({
        dropdownCssClass: 'dp-ob-select2',
        formatInputTooShort(input, min) {
          return `Please enter ${min}or more characters`;
        }
      });
      this.els.select.on('change', () => {
        const selected_opt = this.els.select.find(':selected').first();
        this.addRow(selected_opt.val(), {});
        return this.els.select.select2('val', '0');
      });

      this.updateOptionTypes();

      this.$scope.$watchCollection('optionTypes', () => this.updateOptionTypes());

      // This is a shallow-watch on purpose
      // This builder isnt designed for full model-value syncing like ngModel
      // So this is looking for the actual saveTarget being changed (eg a new object)
      // Otherwise, the object is fully managed internally
      return this.$scope.$watch('saveTarget', () => this.reset());
    }

    reset() {
      let rowId,
        term;
      this.resetTime = (new Date()).getTime();
      if (!this.hasLoaded) { return; }
      this.rowsCount = 0;

      for (const id in this.rows) {
        const row = this.rows[id];
        row.element.remove();
        row.scope.$destroy();
        delete this.rows[id];
      }

      this.els.noOptionsMessage.show();

      const alreadyDone = {};
      if (this.options.fixedExpanded) {
        for (const f of Array.from(this.options.fixedExpanded)) {
          let exist = null;
          let existId = null;
          for (rowId of Object.keys(this.$scope.saveTarget || {})) {
            term = this.$scope.saveTarget[rowId];
            if (term && term.type && (term.type === f)) {
              exist = term;
              existId = rowId;
              alreadyDone[rowId] = true;
              break;
            }
          }

          this.addRow(f, exist, existId, true, (this.options.fixOn != null ? this.options.fixOn.indexOf(f) : undefined) !== -1);
        }
      }

      if (this.$scope.saveTarget) {
        return (() => {
          const result = [];
          for (rowId of Object.keys(this.$scope.saveTarget || {})) {
            term = this.$scope.saveTarget[rowId];
            if (alreadyDone[rowId]) { continue; }
            if (term && term.type) {
              result.push(this.addRow(term.type, term, rowId));
            } else {
              result.push(undefined);
            }
          }
          return result;
        })();
      }
    }

    /*
      * Updates the option types available in the select box
      */
    updateOptionTypes() {
      this.els.select.empty();
      this.els.select.append($('<option value="0" />'));
      return (() => {
        const result = [];
        for (const item of Array.from(this.$scope.optionTypes)) {
          var opt;
          if (item.subOptions != null) {
            const optgroup = $('<optgroup/>').attr('label', item.title);
            for (const subItem of Array.from(item.subOptions)) {
              opt = $('<option/>').val(subItem.value).text(subItem.title);
              optgroup.append(opt);
            }
            result.push(this.els.select.append(optgroup));
          } else {
            opt = $('<option/>').val(item.value).text(item.title);
            result.push(this.els.select.append(opt));
          }
        }
        return result;
      })();
    }

    /*
      * Add a new row to the form
      *
      * @param {String} type
      * @param {Object} value
    */
    addRow(type, value, existId, isFixed, isFixedOn) {
      if (!this.hasLoaded) { return; }
      const def = this.typesDef.getDef(type);

      let tplPromise    = def.getTemplate();
      let dataPromise   = def.getData();
      const dataFormatter = def.getDataFormatter();
      const scopeInit     = (def.scopeInit != null) ? def.scopeInit : null;

      // They might optionally return values rather than promises
      // so wrap in a promise to simplify the api
      if (!this.$q.isPromise(tplPromise)) {
        const retTpl = tplPromise;
        tplPromise = this.$q.fcall(() => retTpl);
      }
      if (!this.$q.isPromise(dataPromise)) {
        const retData = dataPromise;
        dataPromise = this.$q.fcall(() => retData);
      }

      const placeholder = $('<div/>');
      this.els.optionList.append(placeholder);

      const rowIdx = this.rowsCount + 1;
      this.rowsCount += 1;

      const time = (new Date()).getTime();
      this.els.loadingOptionMessage.show().addClass('loading-on');
      var run = (tpl, data, isRetry) => {
        // the row was added before we did our last reset
        let rowId;
        if (time < this.resetTime) { return; }

        let option_title = null;
        for (var v of Array.from(this.$scope.optionTypes)) {
          if (v.subOptions) {
            for (const sb of Array.from(v.subOptions)) {
              if (type === sb.value) {
                option_title = sb.title;
                break;
              }
            }
          } else if (type === v.value) {
            option_title = v.title;
          }
          if (option_title) { break; }
        }

        // sometimes titles may be regenerated elsewhere
        // so we need to wait a bit then try again so we
        // can show the proper title
        if (!option_title && (!isRetry || (isRetry < 20))) {
          this.$timeout(() => run(tpl, data, !isRetry ? 1 : isRetry + 1)
          , 140);
          return;
        }

        const rowScope = this.$scope.$new();
        rowScope.type = type;
        rowScope.type_title = option_title;

        const object = this.typesDef.getVars();
        for (var k of Object.keys(object || {})) {
          v = object[k];
          rowScope[k] = v;
        }

        if (existId) {
          rowId = existId;
        } else {
          rowId = rowScope.$id;
        }

        if (dataFormatter) {
          rowScope.value = value || {};

          rowScope.model = dataFormatter.getViewValue(rowScope.value, data);

          rowScope.$watch('model', () => {
            rowScope.value = dataFormatter.getValue(rowScope.model, data);
            this.$scope.saveTarget[rowId] = rowScope.value;

            if (rowScope.rowOpts.rowEnabled) {
              this.$scope.saveTarget[rowId].DP_DISABLED = false;
              return delete this.$scope.saveTarget[rowId].DP_DISABLED;
            }
            return this.$scope.saveTarget[rowId].DP_DISABLED = true;
          }
          , true);
        } else {
          rowScope.model = {};
          rowScope.value = rowScope.model;

          rowScope.$watch('model', () => {
            rowScope.value = rowScope.model;
            this.$scope.saveTarget[rowId] = rowScope.value;

            if (rowScope.rowOpts.rowEnabled) {
              this.$scope.saveTarget[rowId].DP_DISABLED = false;
              return delete this.$scope.saveTarget[rowId].DP_DISABLED;
            }
            return this.$scope.saveTarget[rowId].DP_DISABLED = true;
          }
          , true);
        }

        if (data) {
          for (k of Object.keys(data || {})) {
            v = data[k];
            rowScope[k] = v;
          }
        }

        if (scopeInit) {
          this.$injector.invoke(scopeInit, this, {
            $scope: rowScope
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

        rowScope.rowOpts.rowIdx = rowIdx;
        rowScope.rowOpts.tagString = this.options.tagString || null;
        rowScope.rowOpts.tagClass = this.options.tagClass || '';
        const element = this.$compile(tpl)(rowScope);

        rowScope.rowFn = {};
        rowScope.rowFn.removeRow = () => this.removeRow(element);

        if (rowScope.rowOpts.withCheckId) {
          element.find('.row-label').attr('for', rowScope.rowOpts.withCheckId);
        }

        rowScope.$emit('rowAdded', this, element, rowScope);

        element.data('scopeId', rowId);
        this.els.loadingOptionMessage.hide().removeClass('loading-on');

        this.els.noOptionsMessage.hide();
        placeholder.replaceWith(element);
        this.rows[rowId] = {
          element,
          scope: rowScope
        };
        this.$scope.saveTarget[rowId] = rowScope.value;

        if (isFixed && !isFixedOn) {
          if (rowScope.rowOpts.rowEnabled) {
            this.$scope.saveTarget[rowId] = rowScope.value;
          } else {
            this.$scope.saveTarget[rowId] = { DP_DISABLED: true };
          }

          return rowScope.$watch('rowOpts.rowEnabled', (rowEnabled) => {
            if (rowEnabled) {
              return delete rowScope.value.DP_DISABLED;
            }
            return rowScope.value.DP_DISABLED = true;
          });
        }
      };

      return this.$q.all([tplPromise, dataPromise]).then((returns) => {
        const tpl  = returns[0];
        const data = returns[1];
        return run(tpl, data);
      });
    }

    /*
      * Removes a row by element
      *
      * @param {HTMLElememnt} row
    */
    removeRow(row) {
      const scopeId = $(row).data('scopeId');
      return this.removeRowById(scopeId);
    }


    /*
      * Removes a row by a scope ID
      *
      * @param {Integer} scopeId
    */
    removeRowById(scopeId) {
      let row = this.rows[scopeId];

      delete this.rows[scopeId];
      delete this.$scope.saveTarget[scopeId];

      row.scope.$emit('rowRemoved', this, row.element, row.scope, this.rowsCount - 1);

      row.element.remove();
      row.scope.$destroy();

      this.rowsCount--;
      if (this.rowsCount === 0) {
        this.els.noOptionsMessage.show();
      } else {
        let idx = 1;
        for (const k of Object.keys(this.rows || {})) {
          row = this.rows[k];
          row.scope.rowOpts.rowIdx = idx;
          idx++;
        }
      }

      return true;
    }
  }
  DeskPRO_OptionBuilder_Controller.initClass();
  return DeskPRO_OptionBuilder_Controller;
});
