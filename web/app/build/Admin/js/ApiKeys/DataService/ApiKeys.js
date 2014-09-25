(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit', 'Admin/ApiKeys/ApiKeyEditFormMapper'], function(BaseListEdit, ApiKeyEditFormMapper) {
    var ApiKeys;
    return ApiKeys = (function(_super) {
      __extends(ApiKeys, _super);

      function ApiKeys() {
        return ApiKeys.__super__.constructor.apply(this, arguments);
      }

      ApiKeys.$inject = ['Api', '$q'];

      ApiKeys.prototype._doLoadList = function() {
        var deferred;
        deferred = this.$q.defer();
        this.Api.sendGet('/api_keys').success((function(_this) {
          return function(data) {
            var models;
            models = data.api_keys;
            return deferred.resolve(models);
          };
        })(this), function(data, status, headers, config) {
          return deferred.reject();
        });
        return deferred.promise;
      };


      /*
       * Remove a model
       *
       * @param {Integer} id
       * @return {promise}
       */

      ApiKeys.prototype.deleteApiKeyById = function(id) {
        var promise;
        promise = this.Api.sendDelete('/api_keys/' + id).success((function(_this) {
          return function() {
            return _this.removeListModelById(id);
          };
        })(this));
        return promise;
      };


      /*
      		 * Get the form mapper
      		 *
      		 * @return {ApiKeyEditFormMapper}
       */

      ApiKeys.prototype.getFormMapper = function() {
        if (this.formMapper) {
          return this.formMapper;
        }
        this.formMapper = new ApiKeyEditFormMapper();
        return this.formMapper;
      };


      /*
       * Get all data needed for the edit page
       *
       * @param {Integer} id
       * @return {promise}
       */

      ApiKeys.prototype.loadEditApiKeyData = function(id) {
        var deferred;
        deferred = this.$q.defer();
        if (id) {
          this.Api.sendGet('/api_keys/' + id).then((function(_this) {
            return function(result) {
              var data;
              data = {};
              data.api_key = result.data.api_key;
              data.all_agents = result.data.api_key.all_agents;
              data.form = _this.getFormMapper().getFormFromModel(data);
              return deferred.resolve(data);
            };
          })(this), function() {
            return deferred.reject();
          });
        } else {
          this.Api.sendGet('/agents').then((function(_this) {
            return function(result) {
              var data;
              data = {};
              data.api_key = {
                person: {}
              };
              data.all_agents = result.data.agents;
              data.form = _this.getFormMapper().getFormFromModel(data);
              return deferred.resolve(data);
            };
          })(this), function() {
            return deferred.reject();
          });
        }
        return deferred.promise;
      };


      /*
       * Saves a form model and merges model with list data
       *
       * @param {Object} model api_key model
       	 * @param {Object} formModel  The model representing the form
       * @return {promise}
       */

      ApiKeys.prototype.saveFormModel = function(model, formModel) {
        var mapper, postData, promise;
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
            if (data.api_key != null) {
              return model = data.api_key;
            }
          });
        }
        promise.success((function(_this) {
          return function() {
            mapper.applyFormToModel(model, formModel);
            return _this.mergeDataModel(model);
          };
        })(this));
        return promise;
      };


      /*
      		 * Generate new API key code
       	 *
       	 * @param {Object} model api_key model
       	 * @param {Object} formModel  The model representing the form
       	 * @return {promise}
       */

      ApiKeys.prototype.regenerateApiKey = function(model, formModel) {
        var promise;
        promise = this.Api.sendPostJson('/api_keys/regenerate/' + model.id);
        promise.success((function(_this) {
          return function(data) {
            formModel.code = data.code;
            return formModel.keyString = data.keyString;
          };
        })(this));
        return promise;
      };

      return ApiKeys;

    })(BaseListEdit);
  });

}).call(this);

//# sourceMappingURL=ApiKeys.js.map
