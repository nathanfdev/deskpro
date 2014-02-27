(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_ChatDeps_Ctrl_List;
    Admin_ChatDeps_Ctrl_List = (function(_super) {
      __extends(Admin_ChatDeps_Ctrl_List, _super);

      function Admin_ChatDeps_Ctrl_List() {
        return Admin_ChatDeps_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Admin_ChatDeps_Ctrl_List.CTRL_ID = 'Admin_ChatDeps_Ctrl_List';

      Admin_ChatDeps_Ctrl_List.CTRL_AS = 'ListCtrl';

      Admin_ChatDeps_Ctrl_List.prototype.init = function() {
        this.depData = this.DataService.get('ChatDeps');
        return this.sortedListOptions = {
          axis: 'y',
          handle: '.drag-handle',
          update: (function(_this) {
            return function(ev, data) {
              var $list, order;
              $list = data.item.closest('ul');
              order = [];
              $list.find('li').each(function() {
                return order.push(parseInt($(this).data('id')));
              });
              _this.depData.saveDisplayOrders(order);
              return _this.pingElement('display_orders');
            };
          })(this)
        };
      };


      /*
      		 * Loads the dep list
       */

      Admin_ChatDeps_Ctrl_List.prototype.initialLoad = function() {
        var promise;
        promise = this.depData.loadList().then((function(_this) {
          return function(list) {
            _this.depList = list;
            return _this.deps = _this.depData.listModels;
          };
        })(this));
        return promise;
      };


      /*
      		 * Show the delete dlg
       */

      Admin_ChatDeps_Ctrl_List.prototype.startDelete = function(for_dep_id) {
        var dep, inst, move_deps_list;
        dep = this.depData.findListModelById(for_dep_id);
        if (dep.children && dep.children.length) {
          this.showAlert("You cannot delete a department with sub-departments. Move or delete the sub-departments first.");
          return;
        }
        move_deps_list = this.depData.getLeafOptionsArray(dep.id);
        if (!move_deps_list.length) {
          this.showAlert('@no_delete_last');
          return;
        }
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('ChatDeps/delete-modal.html'),
          controller: [
            '$scope', '$modalInstance', 'move_deps_list', function($scope, $modalInstance, move_deps_list) {
              $scope.move_deps_list = move_deps_list;
              $scope.selected = {
                move_to_id: move_deps_list[0].id
              };
              $scope.confirm = function() {
                return $modalInstance.close($scope.selected.move_to_id);
              };
              return $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
            }
          ],
          resolve: {
            move_deps_list: (function(_this) {
              return function() {
                return move_deps_list;
              };
            })(this)
          }
        });
        return inst.result.then((function(_this) {
          return function(move_to) {
            return _this.deleteDepartment(dep, move_to);
          };
        })(this));
      };


      /*
      		 * Actually do the delete
       */

      Admin_ChatDeps_Ctrl_List.prototype.deleteDepartment = function(for_dep, move_to) {
        return this.depData.deleteDepartmentById(for_dep.id, move_to).success((function(_this) {
          return function() {
            if (_this.$state.current.name === 'chat.chat_deps.edit' && parseInt(_this.$state.params.id) === for_dep.id) {
              return _this.$state.go('chat.chat_deps');
            }
          };
        })(this)).error((function(_this) {
          return function(info, code) {
            return _this.applyErrorResponseToView(info);
          };
        })(this));
      };

      return Admin_ChatDeps_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_ChatDeps_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map
