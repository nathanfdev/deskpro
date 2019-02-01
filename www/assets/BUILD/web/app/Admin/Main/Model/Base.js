// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS203: Remove `|| {}` from converted for-own loops
 * DS205: Consider reworking code to avoid use of IIFEs
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['angular'], function(angular) {
  /**
    * A model holds data about some kind of entity.
    * Our model class does nothing special except tries to make it easier
    * to dirty-check.
    *
    * Warning: All fields on this object are considered data unless
    * they begin with an underscore. E.g., @title is considered
    * to be a data title on the entity while @_title is considered
    * an internal state representation.
    *
    * Note that we do NOT handle any kind of nesting of models,
    * and these models are completely unaware of other models already loaded.
    * That means you could have two records with ID 5, or a record with a "parent" of 5 etc.
    * These models by themselves are not a repository.
  */
  class Admin_Main_Model_Base {
    constructor(type_id, id_prop) {
      if (id_prop == null) { id_prop = 'id'; }
      this._obj_time   = (new Date()).getTime();
      this._obj_refc   = 0;
      this._type_id    = type_id;
      this._id_prop    = id_prop;
      this._is_mult_id = angular.isArray(id_prop);
      this._dp_uid     = dp_get_uid();
      this._is_model   = true;
      this._data_checkpoints = [];
    }


    /**
      * Add to the ref counter
      *
      * @param {Object} obj Optionally set up auto-release on obj
      */
    retain(obj) {
      this._obj_refc += 1;
      this._obj_time  = (new Date()).getTime();

      if ((obj != null) && (obj._configureAutoReleaseObject != null)) {
        return obj._configureAutoReleaseObject(obj);
      }
    }


    /**
      * Remove from the ref counter
      */
    release() {
      this._obj_refc -= 1;
      return this._obj_time  = (new Date()).getTime();
    }


    /**
      * Copy properties from another model
      */
    copyPropertiesFrom(model) {
      return this.setData(mode.getData());
    }


    /**
      * Get the type of model this is
      *
      * @return {String}
    */
    getTypeId() {
      return this._type_id;
    }

    /**
      * Get the ID of the entity this object represents (typically a numeric ID)
      *
      * @return {Integer}
    */
    getEntityId() {
      if (this[this._id_prop] != null) {
        if (this._is_mult_id) {
          const id_parts = [];
          for (let idp of Array.from(this._id_prop)) {
            id_parts.push(idp);
          }
          return id_parts.join('::');

        } else {
          return this[this._id_prop];
        }
      }

      return null;
    }

    /**
      * Create a new checkpoint. Checkpoints allow you to revert data to previous states or compare
      * with previous states.
      *
      * @param {String} chk_id Optionally provide an ID to refer to the checkpoint later
    */
    setCheckpoint(chk_id = null, deep) {
      if (deep == null) { deep = false; }
      const data = {};
      for (let key of Object.keys(this || {})) {
        const value = this[key];
        if (key.substr(0, 1) !== '_') {
          if ((value != null) && value._is_model) {
            if (deep) {
              value.setCheckpoint(chk_id, true);
            }
          } else {
            data[key] = value;
          }
        }
      }

      return this._data_checkpoints.push([chk_id, data]);
    }


    /**
      * Get data for a checkpoint.
      *
      * @param {String} chk_id Optionally provide an ID, else the latest checkpoint is returned
    */
    getCheckpoint(chk_id = null) {
      if (chk_id) {
        for (let cp of Array.from(this._data_checkpoints)) {
          if (cp[0] === id) { return cp[1]; }
        }
      } else {
        return this._data_checkpoints[this._data_checkpoints.length - 1][1];
      }

      return null;
    }


    /**
      * Revert to a previous checkpoint
      *
      * @param {String} chk_id Optionally provide an ID, else the latest checkpoint is returned
    */
    revertCheckpoint(chk_id = null, deep) {
      let data;
      if (deep == null) { deep = false; }
      if (chk_id) {
        for (let i = 0; i < this._data_checkpoints.length; i++) {
          const cp = this._data_checkpoints[i];
          if (cp[0] === chk_id) {
            data = cp[1];
            this._data_checkpoints = this._data_checkpoints.splice(i, 0);
            break;
          }
        }

        if (!data) {
          throw new Error("No checkpoint found with that ID");
        }
      } else {
        data = this._data_checkpoints.pop();
      }

      this.setCheckpoint();
      this.setData(data);

      if (deep) {
        return (() => {
          const result = [];
          for (let key of Object.keys(this || {})) {
            const value = this[key];
            if (key.substr(0,1) !== '_') {
              if ((value != null) && value._is_model) {
                result.push(value.revertCheckpoint(chk_id, true));
              } else {
                result.push(undefined);
              }
            } else {
              result.push(undefined);
            }
          }
          return result;
        })();
      }
    }


    /**
      * Revert to the first checkpoint (e.g., the initial data)
    */
    revertAllCheckpoints(deep) {
      if (deep == null) { deep = false; }
      const data = this._data_checkpoints.shift();
      this.clearCheckpoints();
      this.setData(data);

      if (deep) {
        return (() => {
          const result = [];
          for (let key of Object.keys(this || {})) {
            const value = this[key];
            if (key.substr(0,1) !== '_') {
              if ((value != null) && value._is_model) {
                result.push(value.revertAllCheckpoints(true));
              } else {
                result.push(undefined);
              }
            } else {
              result.push(undefined);
            }
          }
          return result;
        })();
      }
    }


    /**
      * Clears all checkpoints. The data set now is considered the initial data.
    */
    clearCheckpoints(deep) {
      this._data_checkpoints = [];
      this.setCheckpoint();

      if (deep) {
        return (() => {
          const result = [];
          for (let key of Object.keys(this || {})) {
            const value = this[key];
            if (key.substr(0,1) !== '_') {
              if ((value != null) && value._is_model) {
                result.push(value.clearCheckpoints(true));
              } else {
                result.push(undefined);
              }
            } else {
              result.push(undefined);
            }
          }
          return result;
        })();
      }
    }


    /**
      * Set data on this model
      *
      * @param {Object} data
    */
    setData(data) {
      for (let key of Object.keys(data || {})) {
        const value = data[key];
        this[key] = value;
      }

      if (!this._data_checkpoints.length) {
        return this.setCheckpoint();
      }
    }


    /**
      * Gets all data on this model.
      *
      * @return {Object}
    */
    getData() {
      const data = {};
      for (let key of Object.keys(this || {})) {
        const value = this[key];
        if (key.substr(0, 1) !== '_') {
          data[key] = value;
        }
      }

      return data;
    }


    /**
      * Return an array of field names that have changed.
      *
      * @return {Array}
    */
    getChangedFields(chk_id = null, deep) {
      if (deep == null) { deep = false; }
      const changed = [];

      const last_data = this.getCheckpoint(chk_id);
      if (!last_data) {
        throw new Error("No checkpoint to compare against");
      }

      for (let key of Object.keys(this || {})) {
        const value = this[key];
        if (key.substr(0,1) !== '_') {
          if ((value != null) && value._is_model) {
            if (deep) {
              const model_changed = value.getChangedFields(chk_id, true);
              if (model_changed.length) {
                for (let subchange of Array.from(model_changed)) {
                  changed.push(key + '.' + subchange);
                }
              }
            }
          } else {
            if (value !== last_data[key]) {
              if ((key === 'id') || key.match(/_id$/)) {
                if ((parseInt(value)||0) !== (parseInt(last_data[key])||0)) {
                  changed.push(key);
                }
              } else {
                changed.push(key);
              }
            }
          }
        }
      }

      return changed;
    }
  }
  return Admin_Main_Model_Base;
});