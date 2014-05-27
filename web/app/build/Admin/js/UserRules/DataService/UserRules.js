(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit', 'Admin/UserRules/UserRuleEditFormMapper'], function(BaseListEdit, UserRuleEditFormMapper) {
    var UserRules;
    return UserRules = (function(_super) {
      __extends(UserRules, _super);

      function UserRules() {
        return UserRules.__super__.constructor.apply(this, arguments);
      }

      UserRules.$inject = ['Api', '$q'];

      UserRules.prototype._doLoadList = function() {
        var deferred;
        deferred = this.$q.defer();
        this.Api.sendGet('/user_rules').success((function(_this) {
          return function(data) {
            var models;
            models = data.user_rules;
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

      UserRules.prototype.deleteUserRuleById = function(id) {
        var promise;
        promise = this.Api.sendDelete('/user_rules/' + id).success((function(_this) {
          return function() {
            return _this.removeListModelById(id);
          };
        })(this));
        return promise;
      };


      /*
      		 * Get the form mapper
      		 *
      		 * @return {UserRuleEditFormMapper}
       */

      UserRules.prototype.getFormMapper = function() {
        if (this.formMapper) {
          return this.formMapper;
        }
        this.formMapper = new UserRuleEditFormMapper();
        return this.formMapper;
      };


      /*
      		 * Get all data needed for the edit page
      		 *
      		 * @param {Integer} id
      		 * @return {promise}
       */

      UserRules.prototype.loadEditUserRuleData = function(id) {
        var deferred;
        deferred = this.$q.defer();
        if (id) {
          this.Api.sendDataGet({
            user_rule: "/user_rules/" + id,
            usergroups: '/non_sys_usergroups'
          }).then((function(_this) {
            return function(result) {
              var data;
              data = {};
              data.user_rule = result.data.user_rule.user_rule;
              data.all_usergroups = result.data.usergroups.groups;
              data.form = _this.getFormMapper().getFormFromModel(data);
              return deferred.resolve(data);
            };
          })(this), function() {
            return deferred.reject();
          });
        } else {
          this.Api.sendGet('/non_sys_usergroups').then((function(_this) {
            return function(result) {
              var data;
              data = {};
              data.user_rule = {};
              data.all_usergroups = result.data.groups;
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
      		 * @param {Object} model user_rule model
      		 * @param {Object} formModel  The model representing the form
      		 * @return {promise}
       */

      UserRules.prototype.saveFormModel = function(model, formModel) {
        var mapper, postData, promise;
        mapper = this.getFormMapper();
        postData = mapper.getPostDataFromForm(formModel);
        if (model.id) {
          promise = this.Api.sendPostJson('/user_rules/' + model.id, {
            user_rule: postData
          });
        } else {
          promise = this.Api.sendPutJson('/user_rules', {
            user_rule: postData
          }).success(function(data) {
            return model.id = data.id;
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

      return UserRules;

    })(BaseListEdit);
  });

}).call(this);

//# sourceMappingURL=UserRules.js.map
