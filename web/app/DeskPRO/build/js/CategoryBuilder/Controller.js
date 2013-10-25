(function() {
  define(function() {
    var DeskPRO_CategoryBuilder_Controller;
    return DeskPRO_CategoryBuilder_Controller = (function() {
      function DeskPRO_CategoryBuilder_Controller($scope, $element, $attrs, $compile, $q) {
        var me, tpl,
          _this = this;
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
          update: function(ev, data) {
            return _this.updateOrder();
          }
        };
        tpl = "<div class=\"dp-cb-newrow\">\n	<input type=\"text\" class=\"form-control\" ng-model=\"new_cat_title\" placeholder=\"Enter a title...\" />\n	<span class=\"dp-cb-select-wrap\">\n		<select ng-model=\"new_cat_parent\"\n			ui-select2\n			style=\"min-width:200px;\"\n		>\n			<option value=\"{{c.id}}\" ng-repeat=\"c in parent_cat_list\">{{c.title}}</option>\n		</select>\n	</span>\n	<button class=\"btn dp-cb-addbtn\">Add</button>\n</div>";
        this.addRowEl = this.$compile(tpl)(this.$scope);
        this.addRowEl.appendTo(this.$element);
        this.rootListEl = this.$compile('<ul class="dp-cb-root" ui-sortable="sortedListOptions"></ul>')(this.$scope);
        this.rootListEl.appendTo(this.$element);
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
          var cat, id, idx, k, removeIds, row, _i, _j, _len, _len1, _ref;
          ev.preventDefault();
          row = $(this).closest('li');
          removeIds = [row.data('catId')];
          row.find('li').each(function() {
            return removeIds.push($(this).data('catId'));
          });
          for (_i = 0, _len = removeIds.length; _i < _len; _i++) {
            id = removeIds[_i];
            delete me.cat_rows[id];
            idx = null;
            _ref = me.ngModel.$modelValue;
            for (k = _j = 0, _len1 = _ref.length; _j < _len1; k = ++_j) {
              cat = _ref[k];
              if (cat.id === id) {
                idx = k;
                break;
              }
            }
            if (idx !== null) {
              me.ngModel.$modelValue.splice(idx, 1);
            }
          }
          return row.slideUp(200, function() {
            row.remove();
            return me.updateView(me.ngModel.$modelValue);
          });
        });
      }

      DeskPRO_CategoryBuilder_Controller.prototype.updateOrder = function() {};

      DeskPRO_CategoryBuilder_Controller.prototype.setModel = function(ngModel) {
        var _this = this;
        this.ngModel = ngModel;
        this.cat_rows = {};
        return this.ngModel.$render = function() {
          return _this.updateView(_this.ngModel.$modelValue);
        };
      };

      DeskPRO_CategoryBuilder_Controller.prototype.updateView = function(cats) {
        var cat, old_p, _i, _len;
        for (_i = 0, _len = cats.length; _i < _len; _i++) {
          cat = cats[_i];
          if (this.cat_rows[cat.id] != null) {
            this.cat_rows[cat.id][0].detach();
          } else {
            this.cat_rows[cat.id] = this.renderRow(cat);
          }
        }
        this.$scope.parent_cat_list.length = 0;
        this.$scope.parent_cat_list.push({
          id: 0,
          title: 'No Parent'
        });
        old_p = this.rootListEl.parent();
        this.rootListEl.detach();
        this._procCats(cats, this.rootListEl, 0);
        this.rootListEl.find('.dp-cb-addrow').each(function() {
          var list;
          list = this.parentNode;
          return $(this).detach().appendTo(list);
        });
        return this.rootListEl.prependTo(old_p);
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
        tpl = "<li class=\"dp-cb-row\">\n	<div class=\"dp-cb-titlewrap\">\n		<div class=\"dp-cb-row-move\"><i class=\"icon-reorder\"></i></div>\n		<div class=\"dp-cb-row-controls\">\n			<i class=\"icon-remove remove-trigger\"></i>\n		</div>\n		<div class=\"dp-cb-row-indent\"></div>\n		<input type=\"text\" class=\"form-control dp-cb-input\" ng-model=\"cat.title\" placeholder=\"Enter title...\" />\n	</div>\n	<ul ui-sortable=\"sortedListOptions\"></ul>\n</li>";
        rowScope = this.$scope.$new();
        rowScope.sortedListOptions = this.$scope.sortedListOptions;
        rowScope.cat = cat;
        newRow = this.$compile(tpl)(rowScope);
        newRow.data('catId', cat.id);
        return [newRow, rowScope];
      };

      DeskPRO_CategoryBuilder_Controller.prototype.addCat = function(catData) {
        this.ngModel.$modelValue.push(catData);
        return this.updateView(this.ngModel.$modelValue);
      };

      DeskPRO_CategoryBuilder_Controller.prototype.addNewCatFromTrigger = function(triggerEl) {
        var catData, parent_id, rowEl, title,
          _this = this;
        rowEl = $(triggerEl).closest('.dp-cb-addrow');
        title = $.trim(this.$scope.new_cat_title);
        if (title === '') {
          return;
        }
        parent_id = parseInt(this.$scope.new_cat_parent);
        if (!parent_id) {
          parent_id = null;
        }
        catData = {
          id: _.uniqueId('cb_'),
          "@is_new": true,
          title: title,
          parent_id: parent_id,
          display_order: 0
        };
        if (rowEl.data('parentId')) {
          catData.parent_id = rowEl.data('parentId');
        }
        return this.$scope.$apply(function() {
          return _this.addCat(catData);
        });
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

/*
//@ sourceMappingURL=Controller.js.map
*/