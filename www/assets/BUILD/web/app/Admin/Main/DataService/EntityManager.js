// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS203: Remove `|| {}` from converted for-own loops
 * DS205: Consider reworking code to avoid use of IIFEs
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/DataService/Base',
  'Admin/Main/Model/Base',
  'Admin/Main/Collection/OrderedDictionary'
], function(
  Admin_Main_DataService_Base,
  Admin_Main_Model_Base
)  {
  let Admin_Main_DataService_EntityManager;
  return (Admin_Main_DataService_EntityManager = class Admin_Main_DataService_EntityManager {
    constructor() {
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
    createEntity(type_id, id_prop, data) {
      const entity = this.createUnmanagedEntity(type_id, id_prop, data);
      return this.add(entity, true);
    }


    /**
    * Creates a new managed entity
      *
      * @param {String} type_id
      * @param {String} id_prop
      * @param {Object} data
      * @return {Admin_Main_Model_Base}
    */
    createUnmanagedEntity(type_id, id_prop, data) {
      const entity = new Admin_Main_Model_Base(type_id, id_prop);

      if (data) {
        entity.setData(data);
      }

      return entity;
    }


    /**
    * Adds an entity to the manager
      *
      * @param {Admin_Main_Model_Base} entity
      * @return {Admin_Main_Model_Base} The entity added to the manager
    */
    add(entity, merge) {
      if (merge == null) { merge = false; }
      const entity_type = entity.getTypeId();
      let entity_id   = entity.getEntityId();

      if (!entity_type) { throw new Error("entity must have a type_id"); }
      if (!entity_id) {   throw new Error("entity must have a id_prop"); }

      entity_id = entity_id+"";
      if ((this.entity_cache[entity_type] == null)) {
        this.entity_cache[entity_type] = {};
      }

      let exist_entity = null;
      if (this.entity_cache[entity_type][entity_id] != null) {
        exist_entity = this.entity_cache[entity_type][entity_id];
        if (!merge) {
          throw new Error("entity already exists in the manager");
        }
      }

      if (exist_entity) {
        const object = entity.getData();
        for (let k of Object.keys(object || {})) {
          const v = object[k];
          exist_entity[k] = v;
        }

        return exist_entity;
      }

      this.entity_cache[entity_type][entity_id] = entity;
      return entity;
    }

    /**
    * Copy properties on one object to other similar objects.
    */
    propogate(entity) {
    }

    /**
    * Get an entity from the manager
      *
      * @param {Admin_Main_Model_Base} entity
    * @return {Admin_Main_Model_Base}
    */
    get(entity) {
      const entity_type = entity.getTypeId();
      let entity_id   = entity.getEntityId();

      if (!entity_type) { throw new Error("entity must have a type_id"); }
      if (!entity_id) {   throw new Error("entity must have a id_prop"); }

      entity_id = entity_id+"";
      if (((this.entity_cache[entity_type] != null ? this.entity_cache[entity_type][entity_id] : undefined) == null)) { throw new Error("entity does not exist in manager"); }

      return this.entity_cache[entity_type][entity_id];
    }


    /**
    * Get an entity from the manager
      *
      * @param {Admin_Main_Model_Base} entity
      * @return {Admin_Main_Model_Base}
    */
    getById(entity_type, entity_id) {
      if (!entity_type) { throw new Error("entity must have a type_id"); }
      if (!entity_id) {   throw new Error("entity must have a id_prop"); }
      entity_id = entity_id+"";

      if (((this.entity_cache[entity_type] != null ? this.entity_cache[entity_type][entity_id] : undefined) == null)) { throw new Error("entity does not exist in manager"); }

      return this.entity_cache[entity_type][entity_id];
    }


    /**
    * Remove an entity from the manager
      *
      * @param {Admin_Main_Model_Base} entity
    */
    remove(entity) {
      const entity_type = entity.getTypeId();
      let entity_id   = entity.getEntityId();

      if (!entity_type) { throw new Error("entity must have a type_id"); }
      if (!entity_id) {   throw new Error("entity must have a id_prop"); }
      entity_id = entity_id+"";

      if ((this.entity_cache[entity_type] != null ? this.entity_cache[entity_type][entity_id] : undefined) != null) {
        return delete this.entity_cache[entity_type][entity_id];
      }
    }


    /**
    * Remove an entity from the manager
      *
      * @param {Admin_Main_Model_Base} entity
    */
    removeById(entity_type, entity_id) {
      entity_id = entity_id+"";

      if ((this.entity_cache[entity_type] != null ? this.entity_cache[entity_type][entity_id] : undefined) != null) {
        return delete this.entity_cache[entity_type][entity_id];
      }
    }


    /**
    * Check if the manager has an entity
      *
      * @param {Admin_Main_Model_Base} entity
      * @return {Boolean}
    */
    hasById(entity_type, entity_id) {
      entity_id = entity_id+"";

      if ((this.entity_cache[entity_type] != null ? this.entity_cache[entity_type][entity_id] : undefined) != null) {
        return true;
      }

      return false;
    }


    /**
    * Check if the manager has an entity
      *
      * @param {Admin_Main_Model_Base} entity
      * @return {Boolean}
    */
    has(entity) {
      const entity_type = entity.getTypeId();
      let entity_id   = entity.getEntityId();

      if (!entity_type) { throw new Error("entity must have a type_id"); }
      if (!entity_id) {   throw new Error("entity must have a id_prop and valid ID"); }
      entity_id = entity_id+"";

      if ((this.entity_cache[entity_type] != null ? this.entity_cache[entity_type][entity_id] : undefined) != null) {
        return true;
      }

      return false;
    }


    /**
    * Runs the auto-release which removes entities that are no longer in use
    */
    autoRelease() {
      const time_cut = (new Date()).getTime() - 10000;
      return (() => {
        const result = [];
        for (var type of Object.keys(this.entity_cache || {})) {
          var entities = this.entity_cache[type];
          result.push((() => {
            const result1 = [];
            for (let ent_id of Object.keys(entities || {})) {
              const ent = entities[ent_id];
              if ((ent._obj_refc < 1) && (ent._obj_time < time_cut)) {
                result1.push(delete this.entity_cache[type][ent_id]);
              } else {
                result1.push(undefined);
              }
            }
            return result1;
          })());
        }
        return result;
      })();
    }


    /**
    * Clears entities from the manager
      *
      * @param {String} type A specific type
    */
    clear(type = null) {
      if (type) {
        return delete this.entity_cache[type];
      } else {
        return this.entity_cache = {};
      }
    }
  });
});
