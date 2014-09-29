(function() {
  var __hasProp = {}.hasOwnProperty;

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
    var Admin_Main_Model_Base;
    return Admin_Main_Model_Base = (function() {
      function Admin_Main_Model_Base(type_id, id_prop) {
        if (id_prop == null) {
          id_prop = 'id';
        }
        this._obj_time = (new Date()).getTime();
        this._obj_refc = 0;
        this._type_id = type_id;
        this._id_prop = id_prop;
        this._is_mult_id = angular.isArray(id_prop);
        this._dp_uid = dp_get_uid();
        this._is_model = true;
        this._data_checkpoints = [];
      }


      /**
        	 * Add to the ref counter
        	 *
        	 * @param {Object} obj Optionally set up auto-release on obj
       */

      Admin_Main_Model_Base.prototype.retain = function(obj) {
        this._obj_refc += 1;
        this._obj_time = (new Date()).getTime();
        if ((obj != null) && (obj._configureAutoReleaseObject != null)) {
          return obj._configureAutoReleaseObject(obj);
        }
      };


      /**
        	 * Remove from the ref counter
       */

      Admin_Main_Model_Base.prototype.release = function() {
        this._obj_refc -= 1;
        return this._obj_time = (new Date()).getTime();
      };


      /**
        	 * Copy properties from another model
       */

      Admin_Main_Model_Base.prototype.copyPropertiesFrom = function(model) {
        return this.setData(mode.getData());
      };


      /**
        	* Get the type of model this is
        	*
        	* @return {String}
       */

      Admin_Main_Model_Base.prototype.getTypeId = function() {
        return this._type_id;
      };


      /**
        	* Get the ID of the entity this object represents (typically a numeric ID)
        	*
        	* @return {Integer}
       */

      Admin_Main_Model_Base.prototype.getEntityId = function() {
        var id_parts, idp, _i, _len, _ref;
        if (this[this._id_prop] != null) {
          if (this._is_mult_id) {
            id_parts = [];
            _ref = this._id_prop;
            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
              idp = _ref[_i];
              id_parts.push(idp);
            }
            return id_parts.join('::');
          } else {
            return this[this._id_prop];
          }
        }
        return null;
      };


      /**
        	* Create a new checkpoint. Checkpoints allow you to revert data to previous states or compare
        	* with previous states.
        	*
        	* @param {String} chk_id Optionally provide an ID to refer to the checkpoint later
       */

      Admin_Main_Model_Base.prototype.setCheckpoint = function(chk_id, deep) {
        var data, key, value;
        if (chk_id == null) {
          chk_id = null;
        }
        if (deep == null) {
          deep = false;
        }
        data = {};
        for (key in this) {
          if (!__hasProp.call(this, key)) continue;
          value = this[key];
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
      };


      /**
        	* Get data for a checkpoint.
        	*
        	* @param {String} chk_id Optionally provide an ID, else the latest checkpoint is returned
       */

      Admin_Main_Model_Base.prototype.getCheckpoint = function(chk_id) {
        var cp, _i, _len, _ref;
        if (chk_id == null) {
          chk_id = null;
        }
        if (chk_id) {
          _ref = this._data_checkpoints;
          for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            cp = _ref[_i];
            if (cp[0] === id) {
              return cp[1];
            }
          }
        } else {
          return this._data_checkpoints[this._data_checkpoints.length - 1][1];
        }
        return null;
      };


      /**
        	* Revert to a previous checkpoint
        	*
        	* @param {String} chk_id Optionally provide an ID, else the latest checkpoint is returned
       */

      Admin_Main_Model_Base.prototype.revertCheckpoint = function(chk_id, deep) {
        var cp, data, i, key, value, _i, _len, _ref, _results;
        if (chk_id == null) {
          chk_id = null;
        }
        if (deep == null) {
          deep = false;
        }
        if (chk_id) {
          _ref = this._data_checkpoints;
          for (i = _i = 0, _len = _ref.length; _i < _len; i = ++_i) {
            cp = _ref[i];
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
          _results = [];
          for (key in this) {
            if (!__hasProp.call(this, key)) continue;
            value = this[key];
            if (key.substr(0, 1) !== '_') {
              if ((value != null) && value._is_model) {
                _results.push(value.revertCheckpoint(chk_id, true));
              } else {
                _results.push(void 0);
              }
            } else {
              _results.push(void 0);
            }
          }
          return _results;
        }
      };


      /**
        	* Revert to the first checkpoint (e.g., the initial data)
       */

      Admin_Main_Model_Base.prototype.revertAllCheckpoints = function(deep) {
        var data, key, value, _results;
        if (deep == null) {
          deep = false;
        }
        data = this._data_checkpoints.shift();
        this.clearCheckpoints();
        this.setData(data);
        if (deep) {
          _results = [];
          for (key in this) {
            if (!__hasProp.call(this, key)) continue;
            value = this[key];
            if (key.substr(0, 1) !== '_') {
              if ((value != null) && value._is_model) {
                _results.push(value.revertAllCheckpoints(true));
              } else {
                _results.push(void 0);
              }
            } else {
              _results.push(void 0);
            }
          }
          return _results;
        }
      };


      /**
        	* Clears all checkpoints. The data set now is considered the initial data.
       */

      Admin_Main_Model_Base.prototype.clearCheckpoints = function(deep) {
        var key, value, _results;
        this._data_checkpoints = [];
        this.setCheckpoint();
        if (deep) {
          _results = [];
          for (key in this) {
            if (!__hasProp.call(this, key)) continue;
            value = this[key];
            if (key.substr(0, 1) !== '_') {
              if ((value != null) && value._is_model) {
                _results.push(value.clearCheckpoints(true));
              } else {
                _results.push(void 0);
              }
            } else {
              _results.push(void 0);
            }
          }
          return _results;
        }
      };


      /**
        	* Set data on this model
        	*
        	* @param {Object} data
       */

      Admin_Main_Model_Base.prototype.setData = function(data) {
        var key, value;
        for (key in data) {
          if (!__hasProp.call(data, key)) continue;
          value = data[key];
          this[key] = value;
        }
        if (!this._data_checkpoints.length) {
          return this.setCheckpoint();
        }
      };


      /**
        	* Gets all data on this model.
        	*
        	* @return {Object}
       */

      Admin_Main_Model_Base.prototype.getData = function() {
        var data, key, value;
        data = {};
        for (key in this) {
          if (!__hasProp.call(this, key)) continue;
          value = this[key];
          if (key.substr(0, 1) !== '_') {
            data[key] = value;
          }
        }
        return data;
      };


      /**
        	* Return an array of field names that have changed.
        	*
        	* @return {Array}
       */

      Admin_Main_Model_Base.prototype.getChangedFields = function(chk_id, deep) {
        var changed, key, last_data, model_changed, subchange, value, _i, _len;
        if (chk_id == null) {
          chk_id = null;
        }
        if (deep == null) {
          deep = false;
        }
        changed = [];
        last_data = this.getCheckpoint(chk_id);
        if (!last_data) {
          throw new Error("No checkpoint to compare against");
        }
        for (key in this) {
          if (!__hasProp.call(this, key)) continue;
          value = this[key];
          if (key.substr(0, 1) !== '_') {
            if ((value != null) && value._is_model) {
              if (deep) {
                model_changed = value.getChangedFields(chk_id, true);
                if (model_changed.length) {
                  for (_i = 0, _len = model_changed.length; _i < _len; _i++) {
                    subchange = model_changed[_i];
                    changed.push(key + '.' + subchange);
                  }
                }
              }
            } else {
              if (value !== last_data[key]) {
                if (key === 'id' || key.match(/_id$/)) {
                  if ((parseInt(value) || 0) !== (parseInt(last_data[key]) || 0)) {
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
      };

      return Admin_Main_Model_Base;

    })();
  });

}).call(this);

//# sourceMappingURL=Base.js.map
