define([
  'DeskPRO/Util/Util',
  'DeskPRO/Util/Strings',
  'DeskPRO/Util/Arrays',
  'DeskPRO/Util/Numbers'
], function(
  Util,
  Strings,
  Arrays,
  Numbers
) {
  class DeskPRO_CategoryBuilder_Controller {
    static initClass() {

      this.FACTORY = ['$scope', '$element', '$attrs', '$compile', '$q', '$injector',
        ($scope, $element, $attrs, $compile, $q, $injector) => new DeskPRO_CategoryBuilder_Controller($scope, $element, $attrs, $compile, $q, $injector)
      ];
    }
    constructor($scope, $element, $attrs, $compile, $q, $injector) {
      this.$scope = $scope;
      this.$element = $element;
      this.$attrs = $attrs;
      this.$compile = $compile;
      this.$q = $q;
      this.$injector = $injector;
      this.$scope.categoryBuilder = this;
      this.$scope.new_cat_title = '';
      this.$scope.new_cat_parent = '0';
      this.$scope.parent_cat_list = [];
      this.$scope.sortedListOptions = {
        axis: 'y',
        handle: '.dp-cb-row-move',
        update: (ev, data) => {
          return this.updateOrder();
        }
      };

      this.addRowEl = this.$element.find('.dp-cb-newrow');
      this.rootListEl = this.$element.find('.dp-cb-root');

      const me = this;
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
        let Api;
        ev.preventDefault();
        const row = $(this).closest('li');

        let id = row.data('catId');
        const removeIds = [id];
        const doRemoveIds = [];
        ('cb_' !== id.toString().substr(0, 3)) && doRemoveIds.push(id);

        row.find('li').each(function() {
          id = $(this).data('catId');
          removeIds.push(id);
          return ('cb_' !== id.toString().substr(0, 3)) && doRemoveIds.push(id);
        });

        const doRemove = function() {
          const viewValue = me.ngModel.$viewValue || [];
          for (id of Array.from(removeIds)) {
            delete me.cat_rows[id];
            let idx = null;
            for (let k = 0; k < viewValue.length; k++) {
              const cat = viewValue[k];
              if (cat.id === id) {
                idx = k;
                break;
              }
            }
            if (idx !== null) {
              viewValue.splice(idx, 1);
            }
          }

          return row.slideUp(200, () =>
            me.$scope.$apply(function() {
              row.remove();
              me.ngModel.$setViewValue(viewValue);
              return me.updateView(viewValue);
            })
          );
        };

        // handle delete/update if only field type is defined
        const option = row.data('catId');
        const $modal = me.$injector.get('$modal');
        try {
          Api = me.$injector.get('Api');
        } catch (error) {
          Api = null;
        }

        if (!doRemoveIds.length || !me.$scope.fieldType || !Api) {
          return doRemove();
        }

        return Api.sendDelete('/custom_fields/option', {step: 1, type: me.$scope.fieldType, ids: doRemoveIds}).then(
          function(res) {
            // if nothing to do, just delete
            if (!res.data.success || (res.data.options == null)) { return doRemove(); }

            return $modal.open({
              templateUrl: DP_BASE_ADMIN_URL + '/load-view/' + 'CustomFields/Common/delete-option-modal.html',
              controller:  ['$scope', '$modalInstance', function($scope, $modalInstance) {

                for (let k in res.data.options) {
                  const v = res.data.options[k];
                  if (!me.cat_rows[k]) { delete res.data.options[k]; }
                }

                $scope.dismiss = () => $modalInstance.dismiss();
                $scope.mode = 0;
                $scope.options = res.data.options;
                $scope.update_to = res.data.default;
                $scope.type = me.$scope.fieldType;
                $scope.name = row.children('div').children('input').val();

                return $scope.confirm = function() {
                  if ($scope.mode) {
                    $scope.busy = true;
                    const data = {
                      step:      2,
                      type:      me.$scope.fieldType,
                      ids:       removeIds,
                      update_to: $scope.update_to
                    };
                    Api.sendDelete('/custom_fields/option', data);
                  }
                  doRemove();
                  return $scope.dismiss();
                };
              }
              ]});
          },
          function() {}
        );
      });
    }

    updateOrder() {
      let order = 10;
      const { cat_rows } = this;
      this.$element.find('.dp-cb-row').each(function() {
        const rowId = $(this).data('cat-id');
        if (!rowId || !cat_rows[rowId]) { return; }
        cat_rows[rowId].display_order = order;
        return order += 10;
      });

      const viewValue = this.ngModel.$viewValue || [];
      for (let row of Array.from(viewValue)) {
        const rowId = row.id;
        if (rowId && cat_rows[rowId]) {
          row.display_order = cat_rows[rowId].display_order;
        }
      }

      return this.ngModel.$setViewValue(viewValue);
    }

    setModel(ngModel) {
      this.ngModel = ngModel;
      this.cat_rows = {};
      this.ngModel.$render = () => {
        return this.updateView(this.ngModel.$modelValue);
      };

      this.ngModel.$parsers.push( viewValue => viewValue || []);

      return this.ngModel.$formatters.push( modelValue => modelValue);
    }

    updateView(cats) {
      if (!cats) {
        cats = [];
      }

      for (let cat of Array.from(cats)) {
        if (this.cat_rows[cat.id] != null) {
          this.cat_rows[cat.id][0].detach();
        } else {
          this.cat_rows[cat.id] = this.renderRow(cat);
        }
      }

      const old_parent_opt = this.$scope.new_cat_parent;
      this.$scope.new_cat_parent = 0;
      this.$scope.parent_cat_list = [];
      this.$scope.parent_cat_list = [];
      const old_p = this.rootListEl.parent();
      this.rootListEl.detach();
      this._procCats(cats, this.rootListEl, 0);
      this.rootListEl.find('.dp-cb-addrow').each(function() {
        const list = this.parentNode;
        return $(this).detach().appendTo(list);
      });
      this.rootListEl.prependTo(old_p);
      this.$scope.new_cat_parent = old_parent_opt;

      if (this.$attrs.saveFlatArray) {
        var proc = function(parent_id, title_segs) {
          const select_options = [];
          for (let opt of Array.from(cats)) {
            if (opt.parent_id === parent_id) {
              title_segs.push(opt.title);
              const sub_options = proc(opt.id, title_segs);

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
    }

    _procCats(cats, parentRow, parent_id, parent_titles, depth) {
      if (parent_titles == null) { parent_titles = ''; }
      if (depth == null) { depth = 0; }
      return (() => {
        const result = [];
        for (let cat of Array.from(cats)) {
          var full_title;
          let do_add = false;
          if (!parent_id && !cat.parent_id) {
            cat.depth = 0;
            do_add = true;
          } else if (parent_id && (cat.parent_id === parent_id)) {
            cat.depth = depth;
            do_add = true;
          }

          if (!do_add) { continue; }

          if (parent_titles.length) {
            full_title = parent_titles + ' > ' + cat.title;
          } else {
            full_title = cat.title;
          }

          if ((depth+1) < this.maxDepth) {
            this.$scope.parent_cat_list.push({
              id: cat.id,
              title: full_title
            });
          }

          const row = this.cat_rows[cat.id][0];
          this._procCats(cats, $(row[0]).find('> ul').first(), cat.id, full_title, depth + 1);
          result.push($(row[0]).appendTo(parentRow));
        }
        return result;
      })();
    }

    renderRow(cat) {
      const tpl = `\
<li class="dp-cb-row" data-cat-id="{{cat.id}}">
<div class="dp-cb-titlewrap" style="padding-left: {{ 33 + cat.depth * 15 }}px;">
  <div class="dp-cb-row-move"><i class="fa fa-bars"></i></div>
  <div class="dp-cb-row-controls">
    <i class="fa fa-times-circle remove-trigger"></i>
  </div>
  <div class="dp-cb-row-indent" style="padding-right: 0px; width: {{ cat.depth * 15 }}px;"></div>
  <span class="title-id" title="ID" ng-if="cat.id && !cat['@is_new']">#<span ng-bind="cat.id"></span></span>
  <span class="title-id" title="ID will be generated after you save" ng-if="cat['@is_new']">?</span>
  <input type="text" name="{{ fieldName }}" class="form-control dp-cb-input" ng-model="cat.title" placeholder="Enter title..." />
</div>
<ul ui-sortable="sortedListOptions"></ul>
</li>\
`;

      const rowScope = this.$scope.$new();
      rowScope.sortedListOptions = this.$scope.sortedListOptions;

      rowScope.cat = cat;
      rowScope.fieldName = cat.field_name || rowScope.fieldName;
      const newRow = this.$compile(tpl)(rowScope);
      newRow.data('catId', cat.id);
      return [newRow, rowScope];
    }

    addCat(catData) {
      const viewValue = this.ngModel.$viewValue || [];
      viewValue.push(catData);
      this.ngModel.$setViewValue(viewValue);
      return this.updateView(viewValue);
    }

    getMaxDisplayOrder(parentId) {
      let max = 10;

      const viewValue = this.ngModel.$viewValue || [];
      for (let row of Array.from(viewValue)) {
        if ((parentId && ((row.parent_id != null) && ((row.parent_id+"") === (parentId+"")))) || !parentId) {
          if (row.display_order >= max) {
            max = row.display_order + 10;
          }
        }
      }

      return max;
    }

    addNewCatFromTrigger(triggerEl) {
      const rowEl = $(triggerEl).closest('.dp-cb-addrow');
      const title = Strings.trim(this.$scope.new_cat_title);

      if (title === '') {
        return;
      }

      let parent_id = this.$scope.new_cat_parent;
      if (!parent_id || (parent_id === "") || (parent_id === "0") || (parent_id === 0)) {
        parent_id = null;
      } else if (Numbers.isNumeric(parent_id)) {
        parent_id = parseInt(parent_id);
      }

      const catData = {
        id:        Util.uid('cb_'),
        "@is_new": true,
        title,
        parent_id,
        display_order: this.getMaxDisplayOrder(parent_id)
      };

      if (rowEl.data('parentId')) {
        catData.parent_id = rowEl.data('parentId');
      }

      this.$scope.new_cat_title = '';

      return this.$scope.$apply( () => {
        return this.addCat(catData);
      });
    }


    showDeleteOption() {}
  }
  DeskPRO_CategoryBuilder_Controller.initClass();
  return DeskPRO_CategoryBuilder_Controller;
});
