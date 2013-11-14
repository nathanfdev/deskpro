(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/Base', 'Admin/Main/Model/Base', 'Admin/Main/Collection/OrderedDictionary'], function(Admin_Main_DataService_Base, Admin_Main_Model_Base, Admin_Main_Collection_OrderedDictionary) {
    var Admin_Main_DataService_Departments;
    return Admin_Main_DataService_Departments = (function(_super) {
      __extends(Admin_Main_DataService_Departments, _super);

      function Admin_Main_DataService_Departments(em, Api, $q) {
        Admin_Main_DataService_Departments.__super__.constructor.call(this, em);
        this.$q = $q;
        this.Api = Api;
        this.loadDepListPromise = null;
        this.deps = new Admin_Main_Collection_OrderedDictionary();
        this.parent_to_children = {};
        this.default_dep = null;
      }

      /**
      		* Loads the whole department structure
        	* Returns a promise.
        	* After loaded, you can use getDepartments()
        	*
        	* @return {Promise}
      */


      Admin_Main_DataService_Departments.prototype.loadDepList = function(reload) {
        var deferred, http_def,
          _this = this;
        if (this.loadDepListPromise) {
          return this.loadDepListPromise;
        }
        deferred = this.$q.defer();
        if (!reload && this.deps.count()) {
          this.resetHierarchy();
          deferred.resolve(this.deps);
          return deferred.promise;
        }
        http_def = this.Api.sendGet('/ticket_deps').success(function(data, status, headers, config) {
          _this._setDepData(data.departments);
          _this.default_dep = new Admin_Main_Model_Base();
          _this.default_dep.default_id = data.default_id;
          deferred.resolve(_this.deps);
          return _this.loadDepListPromise = null;
        }, function(data, status, headers, config) {
          deferred.reject();
          return this.loadDepListPromise = null;
        });
        this.loadDepListPromise = deferred.promise;
        return this.loadDepListPromise;
      };

      /**
      		* Adds a new model to the existing department list (eg a dep was just created)
        	*
        	* @return {Admin_Main_Model_Base}
      */


      Admin_Main_DataService_Departments.prototype.addToList = function(dep) {
        var model;
        if (!dep._is_model) {
          model = this.em.createEntity('department', 'id', dep);
        } else {
          model = this.em.add(dep, true);
        }
        this.deps.set(model.id, model);
        return model;
      };

      Admin_Main_DataService_Departments.prototype.resetHierarchy = function() {
        var dep, parent_dep, _i, _j, _len, _len1, _ref, _ref1;
        this.deps.reorder(function(a, b) {
          var order1, order2, _ref;
          order1 = a.display_order || 0;
          order2 = b.display_order || 0;
          if (order1 === order2) {
            return 0;
          }
          return (_ref = order1 < order2) != null ? _ref : -{
            1: 1
          };
        });
        _ref = this.deps.values();
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          dep = _ref[_i];
          delete dep._child_ids;
          if (!dep.parent_id || dep.parent_id === "0") {
            dep.parent_id = 0;
          }
        }
        _ref1 = this.deps.values();
        for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
          dep = _ref1[_j];
          dep.full_title = dep.title;
          if (dep.parent_id) {
            parent_dep = this.deps.get(dep.parent_id);
            if (parent_dep) {
              dep.full_title = parent_dep.title + " > " + dep.title;
              if (this.parent_to_children[parent_dep.id] == null) {
                this.parent_to_children[parent_dep.id] = [];
              }
              this.parent_to_children[parent_dep.id].push(dep.id);
              if (!parent_dep._child_ids) {
                parent_dep._child_ids = [];
              }
              parent_dep._child_ids.push(dep.id);
              dep._depth = 1;
            }
          }
        }
        return this.deps.notifyListeners('changed');
      };

      /**
      		* Initialises the models to keep track of department data
        	*
        	* @return {Promise}
      */


      Admin_Main_DataService_Departments.prototype._setDepData = function(departments) {
        var dep, model, parent_dep, _i, _j, _len, _len1, _ref, _results;
        this.parent_to_children = {};
        this.default_dep = null;
        for (_i = 0, _len = departments.length; _i < _len; _i++) {
          dep = departments[_i];
          if (!dep.parent_id) {
            dep.parent_id = 0;
          }
          model = this.em.createEntity('department', 'id', dep);
          model.retain();
          this.deps.set(model.id, model);
        }
        _ref = this.deps.values();
        _results = [];
        for (_j = 0, _len1 = _ref.length; _j < _len1; _j++) {
          dep = _ref[_j];
          dep.full_title = dep.title;
          if (dep.parent_id) {
            parent_dep = this.deps.get(dep.parent_id);
            if (parent_dep) {
              dep.full_title = parent_dep.title + " > " + dep.title;
              if (this.parent_to_children[parent_dep.id] == null) {
                this.parent_to_children[parent_dep.id] = [];
              }
              this.parent_to_children[parent_dep.id].push(dep.id);
              if (!parent_dep._child_ids) {
                parent_dep._child_ids = [];
              }
              parent_dep._child_ids.push(dep.id);
              _results.push(dep._depth = 1);
            } else {
              _results.push(void 0);
            }
          } else {
            _results.push(void 0);
          }
        }
        return _results;
      };

      /**
      		* Cleans up models that are sitting in memory
      */


      Admin_Main_DataService_Departments.prototype._cleanup = function() {
        var dep, _i, _len, _ref;
        if (this.deps) {
          _ref = this.deps.values();
          for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            dep = _ref[_i];
            dep.release();
          }
        }
        this.deps = null;
        this.parent_to_children = {};
        this.default_dep = null;
      };

      return Admin_Main_DataService_Departments;

    })(Admin_Main_DataService_Base);
  });

}).call(this);

/*
//@ sourceMappingURL=Departments.js.map
*/