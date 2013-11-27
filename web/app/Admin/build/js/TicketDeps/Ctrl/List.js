(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_TicketDeps_Ctrl_List, _ref;
    Admin_TicketDeps_Ctrl_List = (function(_super) {
      __extends(Admin_TicketDeps_Ctrl_List, _super);

      function Admin_TicketDeps_Ctrl_List() {
        _ref = Admin_TicketDeps_Ctrl_List.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketDeps_Ctrl_List.CTRL_ID = 'Admin_TicketDeps_Ctrl_List';

      Admin_TicketDeps_Ctrl_List.CTRL_AS = 'ListCtrl';

      Admin_TicketDeps_Ctrl_List.prototype.init = function() {
        var _this = this;
        this.depData = this.DataService.get('TicketDeps');
        return this.sortedListOptions = {
          axis: 'y',
          handle: '.drag-handle',
          update: function(ev, data) {
            var $list, order;
            $list = data.item.closest('ul');
            order = [];
            $list.find('li').each(function() {
              return order.push(parseInt($(this).data('id')));
            });
            _this.depData.saveDisplayOrders(order);
            return _this.pingElement('display_orders');
          }
        };
      };

      /*
      		# Loads the dep list
      */


      Admin_TicketDeps_Ctrl_List.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.depData.loadList().then(function(list) {
          _this.depList = list;
          return _this.deps = _this.depData.listModels;
        });
        return promise;
      };

      /*
      		# Get the move dep list for use in the delete/move dlg
        	# @return {Array}
      */


      Admin_TicketDeps_Ctrl_List.prototype.getMoveDepList = function(for_dep) {
        var dep, dep_move_list, _i, _len, _ref1;
        dep_move_list = [];
        _ref1 = this.departments;
        for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
          dep = _ref1[_i];
          if (for_dep.id !== dep.id) {
            if (!dep._child_ids) {
              dep_move_list.push(dep);
            }
          }
        }
        return dep_move_list;
      };

      /**
      		# Show the delete dlg
      */


      Admin_TicketDeps_Ctrl_List.prototype.startDelete = function(for_dep_id) {
        var dep, inst, move_deps_list,
          _this = this;
        dep = this.depData.findListModelById(for_dep_id);
        if (dep.children.length) {
          this.showAlert("You cannot delete a department with sub-departments. Move or delete the sub-departments first.");
          return;
        }
        move_deps_list = this.depData.getLeafOptionsArray(dep.id);
        if (!move_deps_list.length) {
          this.showAlert('@no_delete_last');
          return;
        }
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('TicketDeps/delete-modal.html'),
          controller: [
            '$scope', '$modalInstance', 'move_deps_list', function($scope, $modalInstance) {
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
          ]
        });
        return inst.result.then(function(move_to) {
          return _this.deleteDepartment(dep, move_to);
        });
      };

      /*
      		# Actually do th edelete
      */


      Admin_TicketDeps_Ctrl_List.prototype.deleteDepartment = function(for_dep, move_to) {
        var _this = this;
        return this.depData.deleteDepartmentById(for_dep.id, move_to).then(function() {
          if (_this.$state.current.name === 'tickets.ticket_deps.edit' && parseInt(_this.$state.params.id) === for_dep.id) {
            return _this.$state.go('tickets.ticket_deps');
          }
        });
      };

      return Admin_TicketDeps_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_TicketDeps_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=List.js.map
*/