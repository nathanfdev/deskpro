(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/App'], function(Admin_Ctrl_Base) {
    var Admin_TicketDeps_Ctrl_List, _ref;
    Admin_TicketDeps_Ctrl_List = (function(_super) {
      __extends(Admin_TicketDeps_Ctrl_List, _super);

      function Admin_TicketDeps_Ctrl_List() {
        _ref = Admin_TicketDeps_Ctrl_List.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketDeps_Ctrl_List.CTRL_ID = 'Admin_TicketDeps_Ctrl_List';

      Admin_TicketDeps_Ctrl_List.CTRL_AS = 'TicketDepsList';

      Admin_TicketDeps_Ctrl_List.DEPS = ['$rootScope', '$scope', 'DepartmentData', 'em', 'Api', '$state', '$translate', 'Growl'];

      Admin_TicketDeps_Ctrl_List.CTRL_TYPE = 'list';

      Admin_TicketDeps_Ctrl_List.prototype.init = function() {
        var _this = this;
        this.departments_count = 0;
        this.dep_settings = {};
        return this.sortedListOptions = {
          axis: 'y',
          handle: '.drag-handle',
          update: function(ev, data) {
            var $list, postData, promise;
            $list = data.item.closest('ul');
            postData = {
              display_orders: []
            };
            $list.find('li').each(function() {
              return postData.display_orders.push($(this).data('id'));
            });
            promise = _this.Api.sendPostJson('/ticket_deps/display_order', postData);
            return _this.pingElement('display_orders');
          }
        };
      };

      Admin_TicketDeps_Ctrl_List.prototype.initialLoad = function() {
        var data_promise, dep_promise,
          _this = this;
        dep_promise = this.DepartmentData.loadDepList().then(function(departments) {
          _this.initDepList(departments.values());
          return _this.addManagedListener(_this.DepartmentData.deps, 'changed', function() {
            _this.initDepList(_this.DepartmentData.deps.values());
            return _this.ngApply();
          });
        });
        data_promise = this.Api.sendDataGet(['/ticket_deps/settings']).then(function(res) {
          var settings;
          settings = res.data.api_ticket_deps_settings;
          _this.dep_settings.default_id = parseInt(settings['core.default_ticket_dep']) || 0;
          _this.dep_settings.name_singular = settings['core.phrase_department_singular'];
          _this.dep_settings.name_plural = settings['core.phrase_department_plural'];
          if (_this.dep_settings.name_singular || _this.dep_settings.name_plural) {
            return _this.dep_settings.do_rename = true;
          }
        });
        return this.$q.all([dep_promise, data_promise]).then(function() {
          var d, found, _i, _len, _ref1;
          if (_this.dep_settings.default_id) {
            found = false;
            _ref1 = _this.default_dep_list;
            for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
              d = _ref1[_i];
              if (d.id === _this.dep_settings.default_id) {
                found = true;
                break;
              }
            }
            if (!found) {
              _this.dep_settings.default_id = 0;
            }
          }
          if (!_this.dep_settings.default_id || _this.dep_settings.default_id === 0) {
            return _this.dep_settings.default_id = _this.default_dep_list[0].id;
          }
        });
      };

      Admin_TicketDeps_Ctrl_List.prototype.initDepList = function(departments) {
        var dep, _i, _len, _results;
        this.departments = departments;
        this.departments_count = departments.lenght;
        this.parent_deps = [];
        this.child_deps = {};
        this.default_dep_list = [];
        _results = [];
        for (_i = 0, _len = departments.length; _i < _len; _i++) {
          dep = departments[_i];
          if (dep.parent_id) {
            if (!this.child_deps[dep.parent_id]) {
              this.child_deps[dep.parent_id] = [];
            }
            this.child_deps[dep.parent_id].push(dep);
            _results.push(this.default_dep_list.push(dep));
          } else {
            this.parent_deps.push(dep);
            if (!dep._child_ids) {
              _results.push(this.default_dep_list.push(dep));
            } else {
              _results.push(void 0);
            }
          }
        }
        return _results;
      };

      /**
      		* Get the move dep list for use in the delete/move dlg
        	* @return {Array}
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


      Admin_TicketDeps_Ctrl_List.prototype.startDelete = function(for_dep) {
        var inst,
          _this = this;
        if (for_dep._child_ids) {
          this.showAlert("You cannot delete a department with sub-departments. Move or delete the sub-departments first.");
          return;
        }
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('TicketDeps/delete-modal.html'),
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
            move_deps_list: function() {
              return _this.getMoveDepList(for_dep);
            }
          }
        });
        return inst.result.then(function(move_to) {
          return _this.deleteDepartment(for_dep, move_to);
        });
      };

      /**
      		# Actually do th edelete
      */


      Admin_TicketDeps_Ctrl_List.prototype.deleteDepartment = function(for_dep, move_to) {
        var _this = this;
        return this.Api.sendDelete('/ticket_deps/' + for_dep.id, {
          move_to: move_to
        }).success(function() {
          _this.DepartmentData.deps.remove(for_dep.id);
          _this.DepartmentData.resetHierarchy();
          _this.em.removeById('department', for_dep.id);
          _this.ngApply();
          if (_this.$state.current.name === 'tickets.ticket_deps.edit' && parseInt(_this.$state.params.id) === for_dep.id) {
            return _this.$state.go('tickets.ticket_deps');
          }
        });
      };

      Admin_TicketDeps_Ctrl_List.prototype.saveSettings = function() {
        var postData,
          _this = this;
        if (!this.dep_settings.do_rename) {
          this.dep_settings.name_singular = '';
          this.dep_settings.name_plural = '';
        }
        postData = {
          settings: {
            'core.default_ticket_dep': this.dep_settings.default_id,
            'core.phrase_department_singular': this.dep_settings.name_singular,
            'core.phrase_department_plural': this.dep_settings.name_plural
          }
        };
        this.startSpinner('saving_settings');
        return this.Api.sendPostJson('/ticket_deps/settings', postData).then(function() {
          return _this.stopSpinner('saving_settings').then(function() {
            return _this.Growl.success(_this.getRegisteredMessage('saved_settings'));
          });
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