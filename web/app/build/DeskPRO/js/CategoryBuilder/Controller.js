(function() {
  define(['DeskPRO/Util/Util', 'DeskPRO/Util/Strings', 'DeskPRO/Util/Arrays', 'DeskPRO/Util/Numbers'], function(Util, Strings, Arrays, Numbers) {
    var DeskPRO_CategoryBuilder_Controller;
    return DeskPRO_CategoryBuilder_Controller = (function() {
      function DeskPRO_CategoryBuilder_Controller($scope, $element, $attrs, $compile, $q) {
        var me;
        this.$scope = $scope;
        this.$element = $element;
        this.$attrs = $attrs;
        this.$compile = $compile;
        this.$q = $q;
        this.$scope.categoryBuilder = this;
        this.$scope.new_cat_title = '';
        this.$scope.new_cat_parent = '0';
        this.$scope.parent_cat_list = [];
        this.$scope.sortedListOptions = {
          axis: 'y',
          handle: '.dp-cb-row-move',
          update: (function(_this) {
            return function(ev, data) {
              return _this.updateOrder();
            };
          })(this)
        };
        this.addRowEl = this.$element.find('.dp-cb-newrow');
        this.rootListEl = this.$element.find('.dp-cb-root');
        me = this;
        this.$element.on('click', '.dp-cb-addbtn', function(ev) {
          ev.preventDefault();
          return me.addNewCatFromTrigger(this);
        });
        this.maxDepth = 1000;
        if (this.$attrs.maxDepth) {
          this.maxDepth = parseInt(this.$attrs.maxDepth);
        }
        if (this.maxDepth < 1) {
          this.addRowEl.find('.dp-cb-select-wrap').hide();
        }
        this.$element.on('click', '.remove-trigger', function(ev) {
          var cat, id, idx, k, removeIds, row, viewValue, _i, _j, _len, _len1;
          ev.preventDefault();
          row = $(this).closest('li');
          removeIds = [row.data('catId')];
          row.find('li').each(function() {
            return removeIds.push($(this).data('catId'));
          });
          viewValue = me.ngModel.$viewValue || [];
          for (_i = 0, _len = removeIds.length; _i < _len; _i++) {
            id = removeIds[_i];
            delete me.cat_rows[id];
            idx = null;
            for (k = _j = 0, _len1 = viewValue.length; _j < _len1; k = ++_j) {
              cat = viewValue[k];
              if (cat.id === id) {
                idx = k;
                break;
              }
            }
            if (idx !== null) {
              viewValue.splice(idx, 1);
            }
          }
          return row.slideUp(200, function() {
            return me.$scope.$apply(function() {
              row.remove();
              me.ngModel.$setViewValue(viewValue);
              return me.updateView(viewValue);
            });
          });
        });
      }

      DeskPRO_CategoryBuilder_Controller.prototype.updateOrder = function() {};

      DeskPRO_CategoryBuilder_Controller.prototype.setModel = function(ngModel) {
        this.ngModel = ngModel;
        this.cat_rows = {};
        this.ngModel.$render = (function(_this) {
          return function() {
            return _this.updateView(_this.ngModel.$modelValue);
          };
        })(this);
        this.ngModel.$parsers.push(function(viewValue) {
          return viewValue || [];
        });
        return this.ngModel.$formatters.push(function(modelValue) {
          return modelValue;
        });
      };

      DeskPRO_CategoryBuilder_Controller.prototype.updateView = function(cats) {
        var cat, old_p, old_parent_opt, proc, _i, _len;
        if (!cats) {
          cats = [];
        }
        for (_i = 0, _len = cats.length; _i < _len; _i++) {
          cat = cats[_i];
          if (this.cat_rows[cat.id] != null) {
            this.cat_rows[cat.id][0].detach();
          } else {
            this.cat_rows[cat.id] = this.renderRow(cat);
          }
        }
        old_parent_opt = this.$scope.new_cat_parent;
        this.$scope.new_cat_parent = 0;
        this.$scope.parent_cat_list = [];
        this.$scope.parent_cat_list = [];
        old_p = this.rootListEl.parent();
        this.rootListEl.detach();
        this._procCats(cats, this.rootListEl, 0);
        this.rootListEl.find('.dp-cb-addrow').each(function() {
          var list;
          list = this.parentNode;
          return $(this).detach().appendTo(list);
        });
        this.rootListEl.prependTo(old_p);
        this.$scope.new_cat_parent = old_parent_opt;
        if (this.$attrs.saveFlatArray) {
          proc = function(parent_id, title_segs) {
            var opt, select_options, sub_options, _j, _len1;
            select_options = [];
            for (_j = 0, _len1 = cats.length; _j < _len1; _j++) {
              opt = cats[_j];
              if (opt.parent_id === parent_id) {
                title_segs.push(opt.title);
                sub_options = proc(opt.id, title_segs);
                if (sub_options.length) {
                  Arrays.append(select_options, sub_options);
                } else {
                  select_options.push({
                    id: opt.id,
                    title: title_segs.join(' > ')
                  });
                }
                title_segs.pop();
              }
            }
            return select_options;
          };
          return this.$scope.saveFlatArray = proc(null, []);
        }
      };

      DeskPRO_CategoryBuilder_Controller.prototype._procCats = function(cats, parentRow, parent_id, parent_titles, depth) {
        var cat, do_add, full_title, row, _i, _len, _results;
        if (parent_titles == null) {
          parent_titles = '';
        }
        if (depth == null) {
          depth = 0;
        }
        _results = [];
        for (_i = 0, _len = cats.length; _i < _len; _i++) {
          cat = cats[_i];
          do_add = false;
          if (!parent_id && !cat.parent_id) {
            do_add = true;
          } else if (parent_id && cat.parent_id === parent_id) {
            do_add = true;
          }
          if (!do_add) {
            continue;
          }
          if (parent_titles.length) {
            full_title = parent_titles + ' > ' + cat.title;
          } else {
            full_title = cat.title;
          }
          if (depth + 1 < this.maxDepth) {
            this.$scope.parent_cat_list.push({
              id: cat.id,
              title: full_title
            });
          }
          row = this.cat_rows[cat.id][0];
          this._procCats(cats, $(row[0]).find('> ul').first(), cat.id, full_title, depth + 1);
          _results.push($(row[0]).appendTo(parentRow));
        }
        return _results;
      };

      DeskPRO_CategoryBuilder_Controller.prototype.renderRow = function(cat) {
        var newRow, rowScope, tpl;
        tpl = "<li class=\"dp-cb-row\">\n	<div class=\"dp-cb-titlewrap\">\n		<div class=\"dp-cb-row-move\"><i class=\"fa fa-bars\"></i></div>\n		<div class=\"dp-cb-row-controls\">\n			<i class=\"fa fa-times-circle remove-trigger\"></i>\n		</div>\n		<div class=\"dp-cb-row-indent\"></div>\n		<span class=\"title-id\" title=\"ID\" ng-if=\"cat.id && !cat.is_new\">#<span ng-bind=\"cat.id\"></span></span>\n		<span class=\"title-id\" title=\"ID will be generated after you save\" ng-if=\"cat.is_new\">?</span>\n		<input type=\"text\" class=\"form-control dp-cb-input\" ng-model=\"cat.title\" placeholder=\"Enter title...\" />\n	</div>\n	<ul ui-sortable=\"sortedListOptions\"></ul>\n</li>";
        rowScope = this.$scope.$new();
        rowScope.sortedListOptions = this.$scope.sortedListOptions;
        rowScope.cat = cat;
        newRow = this.$compile(tpl)(rowScope);
        newRow.data('catId', cat.id);
        return [newRow, rowScope];
      };

      DeskPRO_CategoryBuilder_Controller.prototype.addCat = function(catData) {
        var viewValue;
        viewValue = this.ngModel.$viewValue || [];
        viewValue.push(catData);
        this.ngModel.$setViewValue(viewValue);
        return this.updateView(viewValue);
      };

      DeskPRO_CategoryBuilder_Controller.prototype.addNewCatFromTrigger = function(triggerEl) {
        var catData, parent_id, rowEl, title;
        rowEl = $(triggerEl).closest('.dp-cb-addrow');
        title = Strings.trim(this.$scope.new_cat_title);
        if (title === '') {
          return;
        }
        parent_id = this.$scope.new_cat_parent;
        if (!parent_id || parent_id === "" || parent_id === "0" || parent_id === 0) {
          parent_id = null;
        } else if (Numbers.isNumeric(parent_id)) {
          parent_id = parseInt(parent_id);
        }
        catData = {
          id: Util.uid('cb_'),
          "@is_new": true,
          title: title,
          parent_id: parent_id,
          display_order: 0
        };
        if (rowEl.data('parentId')) {
          catData.parent_id = rowEl.data('parentId');
        }
        this.$scope.new_cat_title = '';
        return this.$scope.$apply((function(_this) {
          return function() {
            return _this.addCat(catData);
          };
        })(this));
      };

      DeskPRO_CategoryBuilder_Controller.FACTORY = [
        '$scope', '$element', '$attrs', '$compile', '$q', function($scope, $element, $attrs, $compile, $q) {
          return new DeskPRO_CategoryBuilder_Controller($scope, $element, $attrs, $compile, $q);
        }
      ];

      return DeskPRO_CategoryBuilder_Controller;

    })();
  });

}).call(this);

//# sourceMappingURL=Controller.js.map
