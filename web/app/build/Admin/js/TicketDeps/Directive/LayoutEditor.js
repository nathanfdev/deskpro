(function() {
  var __indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; };

  define(['angular', 'DeskPRO/Util/Arrays'], function(angular, Arrays) {
    var InterfaceHandler;
    InterfaceHandler = (function() {
      function InterfaceHandler(scope, element, attr, ngModel, $compile, logger) {
        this.scope = scope;
        this.element = element;
        this.ngModel = ngModel;
        this.$compile = $compile;
        this.logger = logger;
        this.scope.form_tab = 'user';
        this.els = {};
        this.els.user_tab = this.element.find('.user-form');
        this.els.user_worksheet = this.els.user_tab.find('.form-worksheet');
        this.els.agent_tab = this.element.find('.agent-form');
        this.els.agent_worksheet = this.els.agent_tab.find('.form-worksheet');
        this.required_fields = {
          user: [],
          agent: []
        };
        this._initTab('user', this.els.user_tab);
        this._initTab('agent', this.els.agent_tab);
        this.ngModel.$formatters.push((function(_this) {
          return function(modelValue) {
            var f, fieldType, has, i, _i, _j, _k, _l, _len, _len1, _len2, _len3, _len4, _len5, _m, _n, _ref, _ref1, _ref2, _ref3, _ref4, _ref5;
            if (!modelValue) {
              modelValue = {};
            }
            if (modelValue.user == null) {
              modelValue.user = [];
            }
            if (modelValue.agent == null) {
              modelValue.agent = [];
            }
            _ref = _this.required_fields.user;
            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
              fieldType = _ref[_i];
              has = false;
              _ref1 = modelValue.user;
              for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
                f = _ref1[_j];
                if (!f.id) {
                  if (f.field_id) {
                    f.id = "" + f.field_type + "_" + f.field_id;
                  } else {
                    f.id = f.field_type;
                  }
                }
                if (f.field_type === fieldType) {
                  has = true;
                }
              }
              if (!has) {
                modelValue.user.push(_this.createFieldValue(fieldType));
              }
            }
            _ref2 = _this.required_fields.agent;
            for (_k = 0, _len2 = _ref2.length; _k < _len2; _k++) {
              fieldType = _ref2[_k];
              has = false;
              _ref3 = modelValue.agent;
              for (_l = 0, _len3 = _ref3.length; _l < _len3; _l++) {
                f = _ref3[_l];
                if (!f.id) {
                  if (f.field_id) {
                    f.id = "" + f.field_type + "_" + f.field_id;
                  } else {
                    f.id = f.field_type;
                  }
                }
                if (f.field_type === fieldType) {
                  has = true;
                }
              }
              if (!has) {
                modelValue.agent.push(_this.createFieldValue(fieldType));
              }
            }
            _ref4 = modelValue.user;
            for (i = _m = 0, _len4 = _ref4.length; _m < _len4; i = ++_m) {
              f = _ref4[i];
              f.display_order = i;
            }
            _ref5 = modelValue.agent;
            for (i = _n = 0, _len5 = _ref5.length; _n < _len5; i = ++_n) {
              f = _ref5[i];
              f.display_order = i;
            }
            return modelValue;
          };
        })(this));
        this.ngModel.$parsers.push((function(_this) {
          return function(viewModel) {
            return viewModel;
          };
        })(this));
        this.ngModel.$render = (function(_this) {
          return function() {
            return _this.render();
          };
        })(this);
      }

      InterfaceHandler.prototype._initTab = function(tabType, tab) {
        var me, ngModel, requiredFields;
        me = this;
        ngModel = this.ngModel;
        requiredFields = this.required_fields[tabType];
        tab.find('.dp-layout-editor-layout-field').filter('[data-is-required]').each(function() {
          return requiredFields.push($(this).data('field-type'));
        });
        tab.find('.form-elements').find('li.disabled').each(function() {
          var el, parent;
          el = $(this);
          parent = el.closest('ul');
          return el.detach().appendTo(parent);
        });
        tab.find('.form-elements').find('li').not('.disabled').draggable({
          appendTo: 'body',
          helper: 'clone',
          connectToSortable: tab.find('.form-worksheet').find('ul')
        });
        return tab.find('.form-worksheet').find('ul').sortable({
          items: "> li",
          axis: 'y',
          handle: '.drag_handle',
          stop: function(event, ui) {
            var fid, fieldId, fieldRow, fieldType, _ref;
            if ((_ref = ui.item) != null ? _ref.hasClass('dp-layout-editor-layout-field') : void 0) {
              fieldType = ui.item.data('field-type');
              fieldId = ui.item.data('field-id') || null;
              me.logger.debug("[" + tabType + "] Dragged " + fieldType + "_" + (fieldId || '0'));
              fieldRow = me.createAndAddField(tabType, fieldType, fieldId, ui.item);
              ui.item.remove();
              if (fieldRow) {
                fid = fieldRow.data('field-id');
                return tab.find("[data-fid=\"" + fid + "\"]").hide();
              }
            }
          },
          update: function() {
            var f, orderMap, _i, _len, _ref, _results;
            orderMap = {};
            tab.find('.form-worksheet').find('ul').find('li').each(function(i) {
              var fid;
              fid = $(this).data('field-id');
              if (fid) {
                return orderMap[fid] = i;
              }
            });
            if (ngModel.$modelValue[tabType] && ngModel.$modelValue[tabType].length) {
              _ref = ngModel.$modelValue[tabType];
              _results = [];
              for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                f = _ref[_i];
                _results.push(f.display_order = orderMap[f.id] || 0);
              }
              return _results;
            }
          }
        });
      };


      /*
        	 * Create a new field, add it to the model and also add it to the UI
        	 *
        	 * @param {String} tabType
        	 * @param {String} fieldType
        	 * @param {Integer} fieldId
        	 * @param {HTMLElement} insertAfterEl
       */

      InterfaceHandler.prototype.createAndAddField = function(tabType, fieldType, fieldId, insertAfterEl) {
        var f, field, insertAt, row, ul, viewValue, _i, _len, _ref;
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
            this.logger.info("[" + tabType + "] {createAndAddField} Already has " + field.id);
            return null;
          }
        }
        row = this.createFieldRow(tabType, field);
        insertAt = null;
        if (insertAfterEl) {
          insertAt = $(insertAfterEl).parent().find('.layout-field').index(insertAfterEl);
          row.insertAfter(insertAfterEl);
        } else {
          if (tabType === 'user') {
            ul = this.els.user_worksheet.find('ul').first();
          } else {
            ul = this.els.agent_worksheet.find('ul').first();
          }
          ul.append(row);
        }
        if (insertAt === null) {
          viewValue[tabType].push(field);
        } else {
          Arrays.insertAtIndex(viewValue[tabType], field, insertAt);
        }
        this.ngModel.$setViewValue(viewValue);
        return row;
      };


      /*
        	 * Creates a new field object
        	 *
        	 * @return {Object}
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
            on_viewticket_mode: "value",
            on_editticket: true,
            criteria: {
              mode: "all",
              terms: []
            }
          }
        };
        return layoutField;
      };


      /*
        	 * Renders a new field row
        	 *
        	 * @param {Object} field
        	 * @return {HTMLElement}
       */

      InterfaceHandler.prototype.createFieldRow = function(tabType, field) {
        var fieldRow, fieldScope, _ref;
        fieldScope = this.scope.$new(true);
        fieldScope.field = field;
        fieldScope.type = tabType;
        fieldScope.removeRow = (function(_this) {
          return function() {
            var f, idx, tab, viewValue, _i, _len;
            viewValue = _this.ngModel.$viewValue[tabType];
            for (idx = _i = 0, _len = viewValue.length; _i < _len; idx = ++_i) {
              f = viewValue[idx];
              if (f === field) {
                viewValue.splice(idx, 1);
                break;
              }
            }
            fieldRow.remove();
            fieldScope.$destroy();
            tab = _this.els["" + tabType + "_tab"].find('.form-elements');
            return tab.find("[data-fid=\"" + field.id + "\"]").show();
          };
        })(this);
        if (_ref = field.id, __indexOf.call(this.required_fields[tabType], _ref) >= 0) {
          fieldScope.removeRow = function() {};
          fieldScope.isSticky = true;
        }
        fieldRow = this.$compile("<li class=\"layout-field\"><dp-ticket-layout-editor-field type=\"" + tabType + "\" ng-model=\"field\" /></li>")(fieldScope);
        fieldRow.data('field-id', field.id).addClass("field-" + field.id);
        return fieldRow;
      };


      /*
        	 * Renders options on the left (worksheet) with those saved in the model
        	 * Tries to be smart in what it is re-rendering so only changes are rendered.
       */

      InterfaceHandler.prototype.render = function() {
        var doReorder, draggableEls, elementMap, f, field, fieldEl, fieldRow, form, form_model, forms, layoutFieldEls, listEl, newFields, order, orderMap, prevField, prevFieldEl, typeName, worksheetEl, _i, _j, _k, _l, _len, _len1, _len2, _len3, _results;
        forms = [
          {
            typeName: 'user',
            worksheetName: 'user_worksheet'
          }, {
            typeName: 'agent',
            worksheetName: 'agent_worksheet'
          }
        ];
        _results = [];
        for (_i = 0, _len = forms.length; _i < _len; _i++) {
          form = forms[_i];
          typeName = form.typeName;
          form_model = this.ngModel.$viewValue[form.typeName];
          worksheetEl = this.els[form.worksheetName];
          listEl = worksheetEl.find('ul').first();
          layoutFieldEls = worksheetEl.find('.layout-field');
          draggableEls = this.els["" + typeName + "_tab"].find('.form-elements');
          draggableEls.show();
          draggableEls.find('li').each(function() {
            var $el, fid, field_id;
            $el = $(this);
            field_id = $el.data('field-id') || null;
            if (field_id) {
              fid = $el.data('field-type') + '_' + field_id;
            } else {
              fid = $el.data('field-type');
            }
            return $el.data('fid', fid).attr('data-fid', fid);
          });
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
          if (doReorder) {
            layoutFieldEls.detach();
            for (order = _l = 0, _len3 = form_model.length; _l < _len3; order = ++_l) {
              field = form_model[order];
              fieldEl = layoutFieldEls.filter('.field-' + field.id);
              fieldEl.appendTo(listEl);
            }
          }
          _results.push((function() {
            var _len4, _m, _results1;
            _results1 = [];
            for (_m = 0, _len4 = form_model.length; _m < _len4; _m++) {
              f = form_model[_m];
              _results1.push(draggableEls.find("[data-fid=\"" + f.id + "\"]").hide());
            }
            return _results1;
          })());
        }
        return _results;
      };

      return InterfaceHandler;

    })();
    return [
      '$compile', 'LoggerManager', function($compile, LoggerManager) {
        var directive;
        directive = {};
        directive.restrict = 'E';
        directive.require = 'ngModel';
        directive.templateUrl = "TicketDeps/layout-editor.html";
        directive.replace = true;
        directive.scope = {};
        directive.link = function(scope, element, attrs, ngModel) {
          var interfaceHandler, logger;
          logger = LoggerManager.get('directive.dpLayoutEditor');
          return interfaceHandler = new InterfaceHandler(scope, element, attrs, ngModel, $compile, logger);
        };
        return directive;
      }
    ];
  });

}).call(this);

//# sourceMappingURL=LayoutEditor.js.map
