(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit', 'Admin/ApiKeys/ApiKeyEditFormMapper'], function(BaseListEdit, ApiKeyEditFormMapper) {
    var ApiKeys, _ref;
    return ApiKeys = (function(_super) {
      __extends(ApiKeys, _super);

      function ApiKeys() {
        _ref = ApiKeys.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      ApiKeys.$inject = ['Api', '$q'];

      ApiKeys.prototype._doLoadList = function() {
        var deferred,
          _this = this;
        deferred = this.$q.defer();
        this.Api.sendGet('/api_keys').success(function(data) {
          var models;
          models = data.api_keys;
          return deferred.resolve(models);
        }, function(data, status, headers, config) {
          return deferred.reject();
        });
        return deferred.promise;
      };

      /*
      # Remove a model
      #
      # @param {Integer} id
      # @return {promise}
      */


      ApiKeys.prototype.deleteApiKeyById = function(id) {
        var promise,
          _this = this;
        promise = this.Api.sendDelete('/api_keys/' + id).success(function() {
          return _this.removeListModelById(id);
        });
        return promise;
      };

      /*
      		# Get the form mapper
      		#
      		# @return {ApiKeyEditFormMapper}
      */


      ApiKeys.prototype.getFormMapper = function() {
        if (this.formMapper) {
          return this.formMapper;
        }
        this.formMapper = new ApiKeyEditFormMapper();
        return this.formMapper;
      };

      /*
      # Get all data needed for the edit page
      #
      # @param {Integer} id
      # @return {promise}
      */


      ApiKeys.prototype.loadEditApiKeyData = function(id) {
        var deferred,
          _this = this;
        deferred = this.$q.defer();
        if (id) {
          this.Api.sendGet('/api_keys/' + id).then(function(result) {
            var data;
            data = {};
            data.api_key = result.data.api_key;
            data.all_agents = result.data.api_key.all_agents;
            data.form = _this.getFormMapper().getFormFromModel(data);
            return deferred.resolve(data);
          }, function() {
            return deferred.reject();
          });
        } else {
          this.Api.sendGet('/agents').then(function(result) {
            var data;
            data = {};
            data.api_key = {
              user: {}
            };
            data.all_agents = result.data.agents;
            data.form = _this.getFormMapper().getFormFromModel(data);
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
      # @param {Object} model api_key model
       	# @param {Object} formModel  The model representing the form
      # @return {promise}
      */


      ApiKeys.prototype.saveFormModel = function(model, formModel) {
        var mapper, postData, promise,
          _this = this;
        mapper = this.getFormMapper();
        postData = mapper.getPostDataFromForm(formModel);
        if (model.id) {
          promise = this.Api.sendPostJson('/api_keys/' + model.id, {
            api_key: postData
          });
        } else {
          promise = this.Api.sendPutJson('/api_keys', {
            api_key: postData
          }).success(function(data) {
            return model.id = data.id;
          });
        }
        promise.success(function() {
          mapper.applyFormToModel(model, formModel);
          return _this.mergeDataModel(model);
        });
        return promise;
      };

      return ApiKeys;

    })(BaseListEdit);
  });

}).call(this);

/*
//@ sourceMappingURL=ApiKeys.js.map
*/