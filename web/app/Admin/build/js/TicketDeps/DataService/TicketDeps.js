(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit', 'Admin/TicketDeps/TicketDepFormMapper', 'DeskPRO/Util/Arrays', 'DeskPRO/Util/Util'], function(BaseListEdit, TicketDepFormMapper, Arrays, Util) {
    var TicketDeps, _ref;
    return TicketDeps = (function(_super) {
      __extends(TicketDeps, _super);

      function TicketDeps() {
        _ref = TicketDeps.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      TicketDeps.$inject = ['Api', '$q'];

      TicketDeps.prototype._doLoadList = function() {
        var deferred,
          _this = this;
        deferred = this.$q.defer();
        this.Api.sendGet('/ticket_deps').success(function(data, status, headers, config) {
          var models, proc;
          _this.deps = data.departments;
          proc = function(parent) {
            var d, list, parent_id, _i, _len, _ref1;
            list = [];
            parent_id = parent ? parent.id : null;
            _ref1 = data.departments;
            for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
              d = _ref1[_i];
              if (d.parent_id === parent_id) {
                d.parent = parent;
                d.children = proc(d);
                list.push(d);
              }
            }
            return list;
          };
          models = proc(null);
          return deferred.resolve(models);
        }, function(data, status, headers, config) {
          return deferred.reject();
        });
        return deferred.promise;
      };

      /*
        	# Get the form mapper
        	#
        	# @return {TicketDepFormMapper}
      */


      TicketDeps.prototype.getFormMapper = function() {
        if (this.formMapper) {
          return this.formMapper;
        }
        this.formMapper = new TicketDepFormMapper();
        return this.formMapper;
      };

      /*
        	# Get all info needed for an 'edit department' form
        	#
        	# @return {promise}
      */


      TicketDeps.prototype.getEditDepartmentData = function(id) {
        var allPromise, deferred, promise,
          _this = this;
        if (id) {
          promise = this.Api.sendDataGet({
            depInfo: "/ticket_deps/" + id,
            agentsInfo: '/agents',
            agentgroupsInfo: '/agentgroups',
            usergroupsInfo: '/usergroups',
            ticketAccountsInfo: '/ticket_accounts',
            defaultLayoutInfo: '/ticket_layouts/default',
            customLayoutInfo: "/ticket_layouts/" + id
          });
        } else {
          promise = this.Api.sendDataGet({
            agentsInfo: '/agents',
            agentgroupsInfo: '/agentgroups',
            usergroupsInfo: '/usergroups',
            ticketAccountsInfo: '/ticket_accounts',
            defaultLayoutInfo: '/ticket_layouts/default'
          });
        }
        deferred = this.$q.defer();
        allPromise = this.$q.all([promise, this.loadList()]).then(function(result) {
          var d, data, idx, layouts, _i, _len, _ref1;
          result = result[0].data;
          data = {};
          if (result.depInfo) {
            data.dep = result.depInfo.department;
            data.depPerms = result.depInfo.permissions;
          } else {
            data.dep = {};
            data.depPerms = {
              usergroup_ids: [],
              agentgroup_ids: [],
              agent_ids: []
            };
          }
          data.ticket_accounts = result.ticketAccountsInfo.ticket_accounts;
          data.dep_parent_list = _this.listModels.slice(0);
          if (data.dep.id) {
            _ref1 = data.dep_parent_list;
            for (idx = _i = 0, _len = _ref1.length; _i < _len; idx = ++_i) {
              d = _ref1[idx];
              if (d.id === data.dep.id) {
                data.dep_parent_list = Arrays.removeIndex(data.dep_parent_list, idx);
                break;
              }
            }
          }
          data.agents = result.agentsInfo.agents;
          data.agentgroups = result.agentgroupsInfo.agentgroups;
          data.usergroups = result.usergroupsInfo.usergroups;
          layouts = {
            default_layout: result.defaultLayoutInfo.layout,
            custom_layout: result.customLayoutInfo ? result.customLayoutInfo.layout : null,
            use_custom_layout: result.customLayoutInfo && !result.customLayoutInfo.is_default ? true : false
          };
          data.form = _this.getFormMapper().getFormFromModel(data.dep, layouts, data.depPerms, data.agents, data.agentgroups, data.usergroups);
          return deferred.resolve(data);
        });
        return deferred.promise;
      };

      /*
      # Gets an option array of full-title departments.
      #
      # @param {Integer} exclude_id  Dont include this dep in the list
      # @return {Array}
      */


      TicketDeps.prototype.getLeafOptionsArray = function(exclude_id) {
        var list, proc;
        list = [];
        proc = function(coll, title_seg) {
          var d, _i, _len, _results;
          _results = [];
          for (_i = 0, _len = coll.length; _i < _len; _i++) {
            d = coll[_i];
            if (exclude_id && d.id === exclude_id) {
              continue;
            }
            if (!title_seg) {
              title_seg = [];
            }
            title_seg.push(d.title);
            if (d.children && !Util.isEmpty(d.children)) {
              proc(d.children, title_seg);
            } else {
              list.push({
                id: d.id,
                title: title_seg.join(" > ")
              });
            }
            _results.push(title_seg.pop());
          }
          return _results;
        };
        proc(this.listModels);
        return list;
      };

      /*
      # Remove a model from the list by ID.
      #
      # @return {Object/null} The removed object or null if object could not be found
      */


      TicketDeps.prototype.removeListModelById = function(id) {
        var idx, model, removeIdx, result, subModel, _i, _j, _k, _len, _len1, _len2, _ref1, _ref2, _ref3, _ref4;
        if (!this.isListLoaded) {
          return;
        }
        TicketDeps.__super__.removeListModelById.call(this, id);
        if (!this.isListLoaded) {
          return;
        }
        removeIdx = null;
        _ref1 = this.deps;
        for (idx = _i = 0, _len = _ref1.length; _i < _len; idx = ++_i) {
          model = _ref1[idx];
          if (model[this.idProp] === id) {
            removeIdx = idx;
            break;
          }
        }
        result = null;
        if (removeIdx !== null) {
          result = this.deps.splice(removeIdx, 1);
          result = result[0];
        }
        _ref2 = this.listModels;
        for (_j = 0, _len1 = _ref2.length; _j < _len1; _j++) {
          model = _ref2[_j];
          if (!((_ref3 = model.children) != null ? _ref3.length : void 0)) {
            continue;
          }
          removeIdx = null;
          _ref4 = model.children;
          for (idx = _k = 0, _len2 = _ref4.length; _k < _len2; idx = ++_k) {
            subModel = _ref4[idx];
            if (subModel.id === id) {
              removeIdx = idx;
              break;
            }
          }
          if (Util.isNumber(removeIdx)) {
            model.children.splice(removeIdx, 1);
          }
        }
        return result;
      };

      /*
      		# Remove a department
      		#
      		# @param {Integer} id Department id
      		# @param {Integer} move_to - id to which we want to move department data
      	 # @return {promise}
      */


      TicketDeps.prototype.deleteDepartmentById = function(id, move_to) {
        var promise,
          _this = this;
        promise = this.Api.sendDelete('/ticket_deps/' + id, {
          move_to: move_to
        }).success(function() {
          return _this.removeListModelById(id);
        });
        return promise;
      };

      /*
      		# Save display orders
        	#
        	# @param {Array} orders An array of ids in order
        	# @return {promise}
      */


      TicketDeps.prototype.saveDisplayOrders = function(orders) {
        var d, id, order, postData, promise, _i, _len;
        postData = {
          display_orders: []
        };
        for (order = _i = 0, _len = orders.length; _i < _len; order = ++_i) {
          id = orders[order];
          d = this.findListModelById(id);
          if (d) {
            d.display_order = order;
            postData.display_orders.push(id);
          }
        }
        promise = this.Api.sendPostJson('/ticket_deps/display_order', postData);
        return promise;
      };

      /*
        	# Saves a form model and applies the form model to the dep model
        	# once finished.
        	#
        	# @param {Object} dep The dep model
        	# @param {Object} formModel  The model representing the form
        	# @return {promise}
      */


      TicketDeps.prototype.saveFormModel = function(dep, formModel) {
        var mapper, postData, promise,
          _this = this;
        mapper = this.getFormMapper();
        postData = mapper.getPostDataFromForm(formModel);
        if (dep.id) {
          promise = this.Api.sendPostJson('/ticket_deps/' + dep.id, postData);
        } else {
          promise = this.Api.sendPutJson('/ticket_deps', postData).success(function(data) {
            return dep.id = data.department_id;
          });
        }
        promise.success(function() {
          mapper.applyFormToModel(dep, formModel);
          return _this.mergeDataModel(dep);
        });
        return promise;
      };

      return TicketDeps;

    })(BaseListEdit);
  });

}).call(this);

/*
//@ sourceMappingURL=TicketDeps.js.map
*/