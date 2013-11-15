(function() {
  var __hasProp = {}.hasOwnProperty;

  define(['angular'], function(angular) {
    var InterfaceHandler;
    InterfaceHandler = (function() {
      function InterfaceHandler(scope, element, attr, ngModel, $compile) {
        var _this = this;
        this.scope = scope;
        this.element = element;
        this.ngModel = ngModel;
        this.$compile = $compile;
        this.scope.form_tab = 'user';
        this.els = {};
        this.els.user_tab = this.element.find('.user-form');
        this.els.user_worksheet = this.els.user_tab.find('.form-worksheet');
        this.els.agent_tab = this.element.find('.agent-form');
        this.els.agent_worksheet = this.els.agent_tab.find('.form-worksheet');
        this.ngModel.$render = function() {
          return _this.render();
        };
        this._initTab('user', this.els.user_tab);
        this._initTab('agent', this.els.agent_tab);
      }

      InterfaceHandler.prototype._initTab = function(tabType, tab) {
        var me, ngModel, scope;
        me = this;
        ngModel = this.ngModel;
        scope = this.scope;
        tab.find('.form-elements').find('li').draggable({
          appendTo: 'body',
          helper: 'clone',
          connectToSortable: tab.find('.form-worksheet').find('ul')
        });
        return tab.find('.form-worksheet').find('ul').sortable({
          items: "> li",
          axis: 'y',
          handle: '.drag_handle',
          stop: function(event, ui) {
            var _ref;
            if ((_ref = ui.item) != null ? _ref.hasClass('dp-layout-editor-layout-field') : void 0) {
              me.createAndAddField(tabType, ui.item.data('field-type'), ui.item.data('field-id') || null, ui.item);
              return ui.item.remove();
            }
          }
        });
      };

      /*
        	# Create a new field, add it to the model and also add it to the UI
        	#
        	# @param {String} tabType
        	# @param {String} fieldType
        	# @param {Integer} fieldId
        	# @param {HTMLElement} insertAfterEl
      */


      InterfaceHandler.prototype.createAndAddField = function(tabType, fieldType, fieldId, insertAfterEl) {
        var f, field, row, ul, viewValue, _i, _len, _ref;
        if (fieldId == null) {
          fieldId = null;
        }
        if (insertAfterEl == null) {
          insertAfterEl = null;
        }
        viewValue = this.ngModel.$viewValue;
        if (!viewValue) {
          viewValue = {};
        }
        if (!viewValue[tabType]) {
          viewValue[tabType] = [];
        }
        field = this.createFieldValue(fieldType, fieldId || null);
        _ref = viewValue[tabType];
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          f = _ref[_i];
          if (f.id === field.id) {
            return null;
          }
        }
        viewValue[tabType].push(field);
        this.ngModel.$setViewValue(viewValue);
        row = this.createFieldRow(tabType, field);
        if (insertAfterEl) {
          row.insertAfter(insertAfterEl);
        } else {
          if (tabType === 'user') {
            ul = this.els.user_worksheet.find('ul').first();
          } else {
            ul = this.els.agent_worksheet.find('ul').first();
          }
          ul.append(row);
        }
        return row;
      };

      /*
        	# Creates a new field object
        	#
        	# @return {Object}
      */


      InterfaceHandler.prototype.createFieldValue = function(fieldType, fieldId) {
        var id, layoutField;
        if (fieldId == null) {
          fieldId = null;
        }
        id = fieldType;
        if (fieldId) {
          id += '_' + fieldId;
        }
        layoutField = {
          id: id,
          field_type: fieldType,
          field_id: fieldId,
          options: {
            on_newticket: true,
            on_viewticket: true,
            on_viewticket_mode: "VALUE",
            on_editticket: true,
            criteria: {
              mode: "ALL",
              terms: []
            }
          }
        };
        return layoutField;
      };

      /*
        	# Renders a new field row
        	#
        	# @param {Object} field
        	# @return {HTMLElement}
      */


      InterfaceHandler.prototype.createFieldRow = function(tabType, field) {
        var fieldRow, fieldScope, _ref,
          _this = this;
        fieldScope = this.scope.$new(true);
        fieldScope.field = field;
        fieldScope.type = tabType;
        fieldScope.removeRow = function() {
          var f, idx, viewValue, _i, _len;
          viewValue = _this.ngModel.$viewValue[tabType];
          for (idx = _i = 0, _len = viewValue.length; _i < _len; idx = ++_i) {
            f = viewValue[idx];
            if (f === field) {
              viewValue.splice(idx, 1);
              break;
            }
          }
          fieldRow.remove();
          return fieldScope.$destroy();
        };
        if ((_ref = field.id) === 'subject' || _ref === 'message' || _ref === 'user_email') {
          fieldScope.removeRow = function() {};
          fieldScope.isSticky = true;
        }
        fieldRow = this.$compile("<li class=\"layout-field\"><dp-ticket-layout-editor-field type=\"" + tabType + "\" ng-model=\"field\" /></li>")(fieldScope);
        fieldRow.data('field-id', field.id).addClass("field-" + field.id);
        return fieldRow;
      };

      /*
        	# Renders options on the left (worksheet) with those saved in the model
        	# Tries to be smart in what it is re-rendering so only changes are rendered.
      */


      InterfaceHandler.prototype.render = function() {
        var doReorder, elementMap, field, fieldEl, fieldRow, form, form_model, forms, id, layoutFieldEls, listEl, newFields, order, orderMap, prevField, prevFieldEl, stickyFieldIds, stickyFields, tabEl, typeName, worksheetEl, x, _i, _j, _k, _len, _len1, _len2, _ref, _results;
        forms = [
          {
            typeName: 'user',
            modelName: 'user_form',
            worksheetName: 'user_worksheet'
          }, {
            typeName: 'agent',
            modelName: 'agent_form',
            worksheetName: 'agent_worksheet'
          }
        ];
        _results = [];
        for (_i = 0, _len = forms.length; _i < _len; _i++) {
          form = forms[_i];
          if (!this.ngModel.$viewValue) {
            this.ngModel.$viewValue = {};
          }
          if (!this.ngModel.$viewValue[form.modelName]) {
            this.ngModel.$viewValue[form.modelName] = [];
          }
          typeName = form.typeName;
          form_model = (_ref = this.ngModel.$viewValue) != null ? _ref[form.modelName] : void 0;
          worksheetEl = this.els[form.worksheetName];
          tabEl = this.els["" + form.typeName + "_tab"];
          listEl = worksheetEl.find('ul').first();
          stickyFields = tabEl.find('.dp-layout-editor-layout-field').filter('[data-is-required]');
          stickyFieldIds = {};
          stickyFields.each(function() {
            var id;
            id = $(this).data('field-type');
            return stickyFieldIds[id] = $(this);
          });
          layoutFieldEls = worksheetEl.find('.layout-field');
          orderMap = {};
          elementMap = {};
          newFields = [];
          for (order = _j = 0, _len1 = form_model.length; _j < _len1; order = ++_j) {
            field = form_model[order];
            fieldEl = layoutFieldEls.filter('.field-' + field.id);
            if (!fieldEl[0]) {
              newFields.push(field);
            } else {
              elementMap[field.id] = fieldEl;
            }
            orderMap[field.id] = order;
          }
          x = form_model.length;
          for (id in stickyFieldIds) {
            if (!__hasProp.call(stickyFieldIds, id)) continue;
            fieldEl = stickyFieldIds[id];
            if (!elementMap[id]) {
              field = this.createFieldValue(id);
              orderMap[id] = field;
              newFields.push(field);
              x++;
            }
          }
          layoutFieldEls.each(function() {
            var fieldId;
            fieldId = $(this).data('field-id');
            if (!elementMap[fieldId]) {
              return $(this).remove();
            }
          });
          for (_k = 0, _len2 = newFields.length; _k < _len2; _k++) {
            field = newFields[_k];
            fieldRow = this.createFieldRow(typeName, field);
            elementMap[field.id] = fieldRow;
            order = orderMap[field.id];
            if (order === 0) {
              listEl.prepend(fieldRow);
            } else {
              prevField = form_model[order - 1];
              if (prevField) {
                prevFieldEl = elementMap[prevField.id];
                fieldRow.insertAfter(prevFieldEl);
              } else {
                listEl.append(fieldRow);
              }
            }
          }
          doReorder = false;
          layoutFieldEls = worksheetEl.find('.layout-field');
          layoutFieldEls.each(function(currentOrder) {
            var expectedOrder, fieldId;
            fieldId = $(this).data('field-id');
            expectedOrder = orderMap[fieldId] || 0;
            if (currentOrder !== expectedOrder) {
              doReorder = true;
              return false;
            }
          });
          if (doReorder && false) {
            layoutFieldEls.detach();
            _results.push((function() {
              var _l, _len3, _results1;
              _results1 = [];
              for (order = _l = 0, _len3 = form_model.length; _l < _len3; order = ++_l) {
                field = form_model[order];
                fieldEl = layoutFieldEls.filter('.field-' + field.id);
                _results1.push(fieldEl.appendTo(listEl));
              }
              return _results1;
            })());
          } else {
            _results.push(void 0);
          }
        }
        return _results;
      };

      return InterfaceHandler;

    })();
    return [
      '$compile', function($compile) {
        var directive;
        directive = {};
        directive.restrict = 'E';
        directive.require = 'ngModel';
        directive.templateUrl = "TicketDeps/layout-editor.html";
        directive.replace = true;
        directive.link = function(scope, element, attrs, ngModel) {
          var interfaceHandler;
          return interfaceHandler = new InterfaceHandler(scope, element, attrs, ngModel, $compile);
        };
        return directive;
      }
    ];
  });

}).call(this);

/*
//@ sourceMappingURL=LayoutEditor.js.map
*/