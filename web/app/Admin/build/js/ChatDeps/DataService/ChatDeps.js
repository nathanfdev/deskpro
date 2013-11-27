(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit', 'Admin/ChatDeps/ChatDepFormMapper', 'DeskPRO/Util/Arrays'], function(BaseListEdit, ChatDepFormMapper, Arrays) {
    var ChatDeps, _ref;
    return ChatDeps = (function(_super) {
      __extends(ChatDeps, _super);

      function ChatDeps() {
        _ref = ChatDeps.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      ChatDeps.$inject = ['Api', '$q'];

      ChatDeps.prototype._doLoadList = function() {
        var deferred,
          _this = this;
        deferred = this.$q.defer();
        this.Api.sendGet('/chat_deps').success(function(data) {
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
       # @return {ChatDepFormMapper}
      */


      ChatDeps.prototype.getFormMapper = function() {
        if (this.formMapper) {
          return this.formMapper;
        }
        this.formMapper = new ChatDepFormMapper();
        return this.formMapper;
      };

      /*
       # Get all info needed for an 'edit department' form
       #
       # @return {promise}
      */


      ChatDeps.prototype.getEditDepartmentData = function(id) {
        var allPromise, deferred, promise,
          _this = this;
        if (id) {
          promise = this.Api.sendDataGet({
            depInfo: "/chat_deps/" + id,
            agentsInfo: '/agents',
            agentgroupsInfo: '/agentgroups',
            usergroupsInfo: '/usergroups'
          });
        } else {
          promise = this.Api.sendDataGet({
            agentsInfo: '/agents',
            agentgroupsInfo: '/agentgroups',
            usergroupsInfo: '/usergroups'
          });
        }
        deferred = this.$q.defer();
        allPromise = this.$q.all([promise, this.loadList()]).then(function(result) {
          var d, data, idx, _i, _len, _ref1;
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
          data.form = _this.getFormMapper().getFormFromModel(data.dep, data.depPerms, data.agents, data.agentgroups, data.usergroups);
          return deferred.resolve(data);
        });
        return deferred.promise;
      };

      /*
      		# Save display orders
       #
       # @param {Array} orders An array of ids in order
       # @return {promise}
      */


      ChatDeps.prototype.saveDisplayOrders = function(orders) {
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
        promise = this.Api.sendPostJson('/chat_deps/display_order', postData);
        return promise;
      };

      return ChatDeps;

    })(BaseListEdit);
  });

}).call(this);

/*
//@ sourceMappingURL=ChatDeps.js.map
*/