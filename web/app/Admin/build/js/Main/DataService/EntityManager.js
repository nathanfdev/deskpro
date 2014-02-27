(function() {
  var __hasProp = {}.hasOwnProperty;

  define(['Admin/Main/DataService/Base', 'Admin/Main/Model/Base', 'Admin/Main/Collection/OrderedDictionary'], function(Admin_Main_DataService_Base, Admin_Main_Model_Base) {
    var Admin_Main_DataService_EntityManager;
    return Admin_Main_DataService_EntityManager = (function() {
      function Admin_Main_DataService_EntityManager() {
        this.entity_cache = {};
      }


      /**
      		* Creates a new managed entity
        	*
        	* @param {String} type_id
        	* @param {String} id_prop
        	* @param {Object} data
        	* @return {Admin_Main_Model_Base}
       */

      Admin_Main_DataService_EntityManager.prototype.createEntity = function(type_id, id_prop, data) {
        var entity;
        entity = this.createUnmanagedEntity(type_id, id_prop, data);
        return this.add(entity, true);
      };


      /**
      		* Creates a new managed entity
        	*
        	* @param {String} type_id
        	* @param {String} id_prop
        	* @param {Object} data
        	* @return {Admin_Main_Model_Base}
       */

      Admin_Main_DataService_EntityManager.prototype.createUnmanagedEntity = function(type_id, id_prop, data) {
        var entity;
        entity = new Admin_Main_Model_Base(type_id, id_prop);
        if (data) {
          entity.setData(data);
        }
        return entity;
      };


      /**
      		* Adds an entity to the manager
        	*
        	* @param {Admin_Main_Model_Base} entity
        	* @return {Admin_Main_Model_Base} The entity added to the manager
       */

      Admin_Main_DataService_EntityManager.prototype.add = function(entity, merge) {
        var entity_id, entity_type, exist_entity, k, v, _ref;
        if (merge == null) {
          merge = false;
        }
        entity_type = entity.getTypeId();
        entity_id = entity.getEntityId();
        if (!entity_type) {
          throw new Error("entity must have a type_id");
        }
        if (!entity_id) {
          throw new Error("entity must have a id_prop");
        }
        entity_id = entity_id + "";
        if (this.entity_cache[entity_type] == null) {
          this.entity_cache[entity_type] = {};
        }
        exist_entity = null;
        if (this.entity_cache[entity_type][entity_id] != null) {
          exist_entity = this.entity_cache[entity_type][entity_id];
          if (!merge) {
            throw new Error("entity already exists in the manager");
          }
        }
        if (exist_entity) {
          _ref = entity.getData();
          for (k in _ref) {
            if (!__hasProp.call(_ref, k)) continue;
            v = _ref[k];
            exist_entity[k] = v;
          }
          return exist_entity;
        }
        this.entity_cache[entity_type][entity_id] = entity;
        return entity;
      };


      /**
      		* Copy properties on one object to other similar objects.
       */

      Admin_Main_DataService_EntityManager.prototype.propogate = function(entity) {};


      /**
      		* Get an entity from the manager
        	*
        	* @param {Admin_Main_Model_Base} entity
      		* @return {Admin_Main_Model_Base}
       */

      Admin_Main_DataService_EntityManager.prototype.get = function(entity) {
        var entity_id, entity_type, _ref;
        entity_type = entity.getTypeId();
        entity_id = entity.getEntityId();
        if (!entity_type) {
          throw new Error("entity must have a type_id");
        }
        if (!entity_id) {
          throw new Error("entity must have a id_prop");
        }
        entity_id = entity_id + "";
        if (((_ref = this.entity_cache[entity_type]) != null ? _ref[entity_id] : void 0) == null) {
          throw new Error("entity does not exist in manager");
        }
        return this.entity_cache[entity_type][entity_id];
      };


      /**
      		* Get an entity from the manager
        	*
        	* @param {Admin_Main_Model_Base} entity
        	* @return {Admin_Main_Model_Base}
       */

      Admin_Main_DataService_EntityManager.prototype.getById = function(entity_type, entity_id) {
        var _ref;
        if (!entity_type) {
          throw new Error("entity must have a type_id");
        }
        if (!entity_id) {
          throw new Error("entity must have a id_prop");
        }
        entity_id = entity_id + "";
        if (((_ref = this.entity_cache[entity_type]) != null ? _ref[entity_id] : void 0) == null) {
          throw new Error("entity does not exist in manager");
        }
        return this.entity_cache[entity_type][entity_id];
      };


      /**
      		* Remove an entity from the manager
        	*
        	* @param {Admin_Main_Model_Base} entity
       */

      Admin_Main_DataService_EntityManager.prototype.remove = function(entity) {
        var entity_id, entity_type, _ref;
        entity_type = entity.getTypeId();
        entity_id = entity.getEntityId();
        if (!entity_type) {
          throw new Error("entity must have a type_id");
        }
        if (!entity_id) {
          throw new Error("entity must have a id_prop");
        }
        entity_id = entity_id + "";
        if (((_ref = this.entity_cache[entity_type]) != null ? _ref[entity_id] : void 0) != null) {
          return delete this.entity_cache[entity_type][entity_id];
        }
      };


      /**
      		* Remove an entity from the manager
        	*
        	* @param {Admin_Main_Model_Base} entity
       */

      Admin_Main_DataService_EntityManager.prototype.removeById = function(entity_type, entity_id) {
        var _ref;
        entity_id = entity_id + "";
        if (((_ref = this.entity_cache[entity_type]) != null ? _ref[entity_id] : void 0) != null) {
          return delete this.entity_cache[entity_type][entity_id];
        }
      };


      /**
      		* Check if the manager has an entity
        	*
        	* @param {Admin_Main_Model_Base} entity
        	* @return {Boolean}
       */

      Admin_Main_DataService_EntityManager.prototype.hasById = function(entity_type, entity_id) {
        var _ref;
        entity_id = entity_id + "";
        if (((_ref = this.entity_cache[entity_type]) != null ? _ref[entity_id] : void 0) != null) {
          return true;
        }
        return false;
      };


      /**
      		* Check if the manager has an entity
        	*
        	* @param {Admin_Main_Model_Base} entity
        	* @return {Boolean}
       */

      Admin_Main_DataService_EntityManager.prototype.has = function(entity) {
        var entity_id, entity_type, _ref;
        entity_type = entity.getTypeId();
        entity_id = entity.getEntityId();
        if (!entity_type) {
          throw new Error("entity must have a type_id");
        }
        if (!entity_id) {
          throw new Error("entity must have a id_prop and valid ID");
        }
        entity_id = entity_id + "";
        if (((_ref = this.entity_cache[entity_type]) != null ? _ref[entity_id] : void 0) != null) {
          return true;
        }
        return false;
      };


      /**
      		* Runs the auto-release which removes entities that are no longer in use
       */

      Admin_Main_DataService_EntityManager.prototype.autoRelease = function() {
        var ent, ent_id, entities, time_cut, type, _ref, _results;
        time_cut = (new Date()).getTime() - 10000;
        _ref = this.entity_cache;
        _results = [];
        for (type in _ref) {
          if (!__hasProp.call(_ref, type)) continue;
          entities = _ref[type];
          _results.push((function() {
            var _results1;
            _results1 = [];
            for (ent_id in entities) {
              ent = entities[ent_id];
              if (ent._obj_refc < 1 && ent._obj_time < time_cut) {
                _results1.push(delete this.entity_cache[type][ent_id]);
              } else {
                _results1.push(void 0);
              }
            }
            return _results1;
          }).call(this));
        }
        return _results;
      };


      /**
      		* Clears entities from the manager
        	*
        	* @param {String} type A specific type
       */

      Admin_Main_DataService_EntityManager.prototype.clear = function(type) {
        if (type == null) {
          type = null;
        }
        if (type) {
          return delete this.entity_cache[type];
        } else {
          return this.entity_cache = {};
        }
      };

      return Admin_Main_DataService_EntityManager;

    })();
  });

}).call(this);

//# sourceMappingURL=EntityManager.js.map
