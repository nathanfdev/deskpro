(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit'], function(BaseListEdit) {
    var Bans, _ref;
    return Bans = (function(_super) {
      __extends(Bans, _super);

      function Bans() {
        _ref = Bans.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Bans.$inject = ['Api', '$q'];

      Bans.type = 'ip';

      /*
       	#
      */


      Bans.prototype.init = function() {
        return this.setSubLists(['ip_bans', 'email_bans']);
      };

      /*
       	#
      */


      Bans.prototype._doLoadList = function() {
        var deferred,
          _this = this;
        deferred = this.$q.defer();
        this.Api.sendGet('/banning').success(function(data) {
          var models;
          models = data.bans;
          return deferred.resolve(models);
        }, function(data, status, headers, config) {
          return deferred.reject();
        });
        return deferred.promise;
      };

      /*
       	# Sets type of ban that is used for create / update / delete operations
      		#
       	# @param {string} type
      */


      Bans.prototype.setType = function(type) {
        return this.type = type;
      };

      /*
      # Remove a model
      #
      # @param {Integer} id
      # @return {promise}
      */


      Bans.prototype.deleteBanById = function(id) {
        var promise,
          _this = this;
        promise = this.Api.sendDelete('/banning_' + this.type + '/' + id).success(function() {
          return _this.removeListModelById(id);
        });
        return promise;
      };

      /*
      # Get all data needed for the edit page
      #
      # @param {Integer} id
      # @return {promise}
      */


      Bans.prototype.loadEditBanData = function(id) {
        var deferred,
          _this = this;
        deferred = this.$q.defer();
        if (id) {
          this.Api.sendGet('/banning_' + this.type + '/' + id).then(function(result) {
            var data;
            data = {};
            data[_this.type + '_ban'] = result.data[_this.type + '_ban'];
            data.form = data;
            return deferred.resolve(data);
          }, function() {
            return deferred.reject();
          });
        } else {
          this.Api.sendGet('/banning_' + this.type).then(function(result) {
            var data;
            data = {};
            data[_this.type + '_ban'] = {};
            data.form = data;
            return deferred.resolve(data);
          }, function() {
            return deferred.reject();
          });
        }
        return deferred.promise;
      };

      /*
      # Saves a form model and merges model with list data
      #
      # @param {Object} model
       	# @param {Object} formModel  The model representing the form
      # @return {promise}
      */


      Bans.prototype.saveFormModel = function(model, formModel) {
        var postData, promise, sendData,
          _this = this;
        postData = formModel;
        sendData = {};
        sendData[this.type + '_ban'] = postData;
        if (model.id) {
          promise = this.Api.sendPostJson('/banning_' + this.type + '/' + model.id, sendData);
        } else {
          promise = this.Api.sendPutJson('/banning_' + this.type, sendData).success(function(data) {
            return model.id = data.id;
          });
        }
        promise.success(function() {
          return _this.mergeDataModel(model);
        });
        return promise;
      };

      return Bans;

    })(BaseListEdit);
  });

}).call(this);

/*
//@ sourceMappingURL=Bans.js.map
*/