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
          models = data.groups;
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
      		# Get all data needed for the edit page. Also returns the "reg_group" usergroup info as well.
      		#
      		# @param {Integer} id
      		# @return {promise}
      */


      UserGroups.prototype.loadEditUserGroupData = function(id) {
        var deferred, sendTypes,
          _this = this;
        deferred = this.$q.defer();
        sendTypes = {
          reg_group: '/user_groups/1'
        };
        if (id && id !== 1) {
          sendTypes.group = "/user_groups/" + id;
        }
        this.Api.sendDataGet(sendTypes).then(function(result) {
          var data;
          data = {};
          data.reg_group = result.data.reg_group.group;
          if (result.data.group) {
            data.group = result.data.group.group;
            data.form = _this.getFormMapper().getFormFromModel(data);
          } else {
            data.group = {
              id: null,
              title: '',
              is_enabled: true
            };
            data.group.perms = {};
            data.form = _this.getFormMapper().getFormFromModel(data);
          }
          return deferred.resolve(data);
        }, function() {
          return deferred.reject();
        });
        return deferred.promise;
      };

      /*
      		# Saves a form model and merges model with list data
      		#
      		# @param {Object} model api_key model
      		# @param {Object} formModel  The model representing the form
      		# @return {promise}
      */


      UserGroups.prototype.saveFormModel = function(model, formModel, formPermsModel) {
        var mapper, postData, promise,
          _this = this;
        mapper = this.getFormMapper();
        postData = mapper.getPostDataFromForm(formModel, formPermsModel);
        if (model.id) {
          promise = this.Api.sendPostJson('/user_groups/' + model.id, {
            group: postData
          });
        } else {
          promise = this.Api.sendPutJson('/user_groups', {
            group: postData
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