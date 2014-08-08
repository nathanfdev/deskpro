(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/Base', 'Admin/Main/Model/Base', 'Admin/Main/Collection/OrderedDictionary'], function(Admin_Main_DataService_Base, Admin_Main_Model_Base, Admin_Main_Collection_OrderedDictionary) {
    var Admin_ChannelSms_DataService_SmsAccounts;
    return Admin_ChannelSms_DataService_SmsAccounts = (function(_super) {
      __extends(Admin_ChannelSms_DataService_SmsAccounts, _super);

      function Admin_ChannelSms_DataService_SmsAccounts(em, Api, $q) {
        Admin_ChannelSms_DataService_SmsAccounts.__super__.constructor.call(this, em);
        this.$q = $q;
        this.Api = Api;
        this.loadListPromise = null;
        this.recs = new Admin_Main_Collection_OrderedDictionary();
      }


      /**
      		* Loads list of accounts
      	*
      	* @return {Promise}
       */

      Admin_ChannelSms_DataService_SmsAccounts.prototype.loadList = function(reload) {
        var deferred, http_def;
        if (this.loadListPromise) {
          return this.loadListPromise;
        }
        deferred = this.$q.defer();
        if (!reload && this.recs.count()) {
          deferred.resolve(this.recs);
          return deferred.promise;
        }
        http_def = this.Api.sendGet('/channel/sms/accounts').success((function(_this) {
          return function(data, status, headers, config) {
            _this._setListData(data.sms_accounts);
            return deferred.resolve(_this.recs);
          };
        })(this), function(data, status, headers, config) {
          return deferred.reject();
        });
        this.loadListPromise = deferred.promise;
        return this.loadListPromise;
      };

      Admin_ChannelSms_DataService_SmsAccounts.prototype.remove = function(id) {
        this.recs.remove(id);
        return this.em.removeById('sms_account', 'id');
      };

      Admin_ChannelSms_DataService_SmsAccounts.prototype._setListData = function(raw_recs) {
        var model, rec, _i, _len, _results;
        _results = [];
        for (_i = 0, _len = raw_recs.length; _i < _len; _i++) {
          rec = raw_recs[_i];
          console.log(rec);
          model = this.em.createEntity('sms_account', 'id', rec);
          console.log(model);
          model.retain();
          _results.push(this.recs.set(model.id, model));
        }
        return _results;
      };


      /*
      	 * Updates the first-class model (title, etc)
      	 * with account provided. Or adds it to the list if it doesnt exist.
       */

      Admin_ChannelSms_DataService_SmsAccounts.prototype.updateModel = function(account) {
        var new_model;
        new_model = this.em.createEntity('sms_account', 'id', account);
        this.recs.set(new_model.id, new_model);
        return new_model;
      };


      /**
      		* Adds a new model to the existing list (eg was just created)
      	*
      	* @return {Admin_Main_Model_Base}
       */

      Admin_ChannelSms_DataService_SmsAccounts.prototype.addToList = function(rec) {
        var model;
        if (!rec._is_model) {
          model = this.em.createEntity('sms_account', 'id', dep);
        } else {
          model = this.em.add(rec, true);
        }
        this.recs.set(model.id, model);
        return model;
      };

      return Admin_ChannelSms_DataService_SmsAccounts;

    })(Admin_Main_DataService_Base);
  });

}).call(this);

//# sourceMappingURL=SmsAccounts.js.map
