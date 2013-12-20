(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit', 'Admin/UserGroups/UserGroupEditFormMapper'], function(BaseListEdit, UserGroupEditFormMapper) {
    var UserGroups, _ref;
    return UserGroups = (function(_super) {
      __extends(UserGroups, _super);

      function UserGroups() {
        _ref = UserGroups.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      UserGroups.$inject = ['Api', '$q'];

      UserGroups.prototype._doLoadList = function() {
        var deferred,
          _this = this;
        deferred = this.$q.defer();
        this.Api.sendGet('/user_groups').success(function(data) {
          var models;
          models = data.user_groups;
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


      UserGroups.prototype.deleteUserGroupById = function(id) {
        var promise,
          _this = this;
        promise = this.Api.sendDelete('/user_groups/' + id).success(function() {
          return _this.removeListModelById(id);
        });
        return promise;
      };

      /*
      		# Get the form mapper
      		#
      		# @return {UserGroupEditFormMapper}
      */


      UserGroups.prototype.getFormMapper = function() {
        if (this.formMapper) {
          return this.formMapper;
        }
        this.formMapper = new UserGroupEditFormMapper();
        return this.formMapper;
      };

      /*
      # Get all data needed for the edit page
      #
      # @param {Integer} id
      # @return {promise}
      */


      UserGroups.prototype.loadEditUserGroupData = function(id) {
        var data, deferred,
          _this = this;
        deferred = this.$q.defer();
        if (id) {
          this.Api.sendGet('/user_groups/' + id).then(function(result) {
            var data;
            data = {};
            data.user_group = result.data.user_group;
            data.form = _this.getFormMapper().getFormFromModel(data);
            return deferred.resolve(data);
          }, function() {
            return deferred.reject();
          });
        } else {
          data = {};
          data.user_group = {
            is_enabled: true
          };
          data.user_group.permissions = {};
          data.form = this.getFormMapper().getFormFromModel(data);
          deferred.resolve(data);
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


      UserGroups.prototype.saveFormModel = function(model, formModel) {
        var mapper, postData, promise,
          _this = this;
        mapper = this.getFormMapper();
        postData = mapper.getPostDataFromForm(formModel);
        if (model.id) {
          promise = this.Api.sendPostJson('/user_groups/' + model.id, {
            user_group: postData
          });
        } else {
          promise = this.Api.sendPutJson('/user_groups', {
            user_group: postData
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

      return UserGroups;

    })(BaseListEdit);
  });

}).call(this);

/*
//@ sourceMappingURL=UserGroups.js.map
*/