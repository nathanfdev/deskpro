(function() {
  define(function() {
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
        this.rootListEl = this.$element.find('.dp-cb-root');
        me = this;
        this.$element.on('click', '.dp-cb-addbtn', function(ev) {
          ev.preventDefault();
          return me.addNewCatFromTrigger(this);
        });
      }

      DeskPRO_CategoryBuilder_Controller.prototype.setModel = function(ngModel) {
        var _this = this;
        this.ngModel = ngModel;
        this.cat_rows = {};
        return this.ngModel.render = function() {
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
        old_p = this.rootListEl.parent();
        this.rootListEl.detach();
        this._procCats(cats, this.rootListEl, null);
        return this.rootListEl.appendTo(old_p);
      };

      DeskPRO_CategoryBuilder_Controller.prototype._procCats = function(cats, parentRow, parent_id) {
        var cat, do_add, row, _i, _len, _results;
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
          row = this.cat_rows[cat.id][0];
          this._procCats(cats, row[0], cat.id);
          _results.push($(row[0]).appendTo(parentRow));
        }
        return _results;
      };

      DeskPRO_CategoryBuilder_Controller.prototype.renderRow = function(cat) {
        var newRow, rowScope, tpl;
        tpl = "<li class=\"dp-cb-row\">\n	<input type=\"text\" class=\"form-control input-sm\" ng-model=\"cat.title\" />\n	<ul>\n		<li class=\"dp-cb-addrow\">\n			<div class=\"dp-cb-rowwrap\">\n				<input type=\"text\" class=\"form-control input-sm\" />\n				<button class=\"btn btn-xs dp-cb-addbtn\">Add</button>\n			</div>\n		</li>\n	</ul>\n</li>";
        rowScope = this.$scope.$new();
        rowScope.cat = cat;
        newRow = this.$compile(tpl)(rowScope);
        newRow.find('.dp-cb-addrow').data('parent-id', cat.id);
        return [newRow, rowScope];
      };

      DeskPRO_CategoryBuilder_Controller.prototype.addCat = function(catData) {
        this.ngModel.$modelValue.push(catData);
        return this.updateView(this.ngModel.$modelValue);
      };

      DeskPRO_CategoryBuilder_Controller.prototype.addNewCatFromTrigger = function(triggerEl) {
        var catData, rowEl, title,
          _this = this;
        rowEl = $(triggerEl).closest('.dp-cb-addrow');
        title = $.trim(rowEl.find('input').val() || '');
        if (title === '') {
          return;
        }
        catData = {
          id: _.uniqueId('cb_'),
          is_new: true,
          title: title,
          parent_id: null,
          display_order: 0
        };
        if (rowEl.data('parent_id')) {
          catData.parent_id = rowEl.data('parent_id');
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