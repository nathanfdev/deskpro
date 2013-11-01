(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/Base', 'Admin/Main/Model/Base', 'Admin/Main/Collection/OrderedDictionary'], function(Admin_Main_DataService_Base, Admin_Main_Model_Base, Admin_Main_Collection_OrderedDictionary) {
    var Admin_TicketTriggers_DataService_Triggers;
    return Admin_TicketTriggers_DataService_Triggers = (function(_super) {
      __extends(Admin_TicketTriggers_DataService_Triggers, _super);

      function Admin_TicketTriggers_DataService_Triggers(type, em, Api, $q) {
        Admin_TicketTriggers_DataService_Triggers.__super__.constructor.call(this, em);
        this.type = type;
        this.$q = $q;
        this.Api = Api;
        this.loadListPromise = null;
        this.recs = new Admin_Main_Collection_OrderedDictionary();
      }

      /*
      		# Loads list of accounts
        	#
        	# @return {Promise}
      */


      Admin_TicketTriggers_DataService_Triggers.prototype.loadList = function(reload) {
        var deferred, http_def,
          _this = this;
        if (this.loadListPromise) {
          return this.loadListPromise;
        }
        deferred = this.$q.defer();
        if (!reload && this.recs.count()) {
          deferred.resolve(this.recs);
          return deferred.promise;
        }
        http_def = this.Api.sendGet("/ticket_triggers/" + this.type).success(function(data) {
          _this._setListData(data.triggers);
          return deferred.resolve(_this.recs);
        }, function(data, status, headers, config) {
          return deferred.reject();
        });
        this.loadListPromise = deferred.promise;
        return this.loadListPromise;
      };

      Admin_TicketTriggers_DataService_Triggers.prototype._setListData = function(triggers) {
        this.recs.clear();
        return this.recs.addArray(triggers);
      };

      Admin_TicketTriggers_DataService_Triggers.prototype.loadTrigger = function(triggerId) {
        var deferred;
        deferred = this.$q.defer();
        this.Api.sendGet("/ticket_triggers/" + triggerId).success(function(data) {
          return deferred.resolve(data.trigger);
        });
        return deferred.promise;
      };

      /*
        	# Removes a record
      */


      Admin_TicketTriggers_DataService_Triggers.prototype.removeTriggerModel = function(id) {
        return this.recs.remove(id);
      };

      /*
        	# Updates the first-class model (title, etc)
        	# with account provided. Or adds it to the list if it doesnt exist.
      */


      Admin_TicketTriggers_DataService_Triggers.prototype.updateTriggerModel = function(model) {
        var exist, k, v;
        exist = this.recs.get(model.id);
        if (exist) {
          for (k in model) {
            if (!__hasProp.call(model, k)) continue;
            v = model[k];
            exist[k] = v;
          }
        } else {
          this.recs.set(model, model);
        }
        return model;
      };

      /**
      		* Adds a new model to the existing list (eg was just created)
        	*
        	* @return {Admin_Main_Model_Base}
      */


      Admin_TicketTriggers_DataService_Triggers.prototype.addTriggerModel = function(model) {
        this.recs.set(model.id, model);
        return model;
      };

      return Admin_TicketTriggers_DataService_Triggers;

    })(Admin_Main_DataService_Base);
  });

}).call(this);

/*
//@ sourceMappingURL=Triggers.js.map
*/