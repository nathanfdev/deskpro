(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit', 'Admin/TwitterAccounts/TwitterAccountEditFormMapper'], function(BaseListEdit, TwitterAccountEditFormMapper) {
    var Admin_TwitterAccounts_DataService_TwitterAccounts, _ref;
    return Admin_TwitterAccounts_DataService_TwitterAccounts = (function(_super) {
      __extends(Admin_TwitterAccounts_DataService_TwitterAccounts, _super);

      function Admin_TwitterAccounts_DataService_TwitterAccounts() {
        _ref = Admin_TwitterAccounts_DataService_TwitterAccounts.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TwitterAccounts_DataService_TwitterAccounts.$inject = ['Api', '$q'];

      Admin_TwitterAccounts_DataService_TwitterAccounts.prototype._doLoadList = function() {
        var deferred,
          _this = this;
        deferred = this.$q.defer();
        this.Api.sendGet('/twitter_accounts').success(function(data) {
          var models;
          models = data.twitter_accounts;
          return deferred.resolve(models);
        }, function(data, status, headers, config) {
          return deferred.reject();
        });
        return deferred.promise;
      };

      /*
        	# Remove a model
        	#
        	# @param {Integer} id twitter_account id
        	# @return {promise}
      */


      Admin_TwitterAccounts_DataService_TwitterAccounts.prototype.deleteTwitterAccountById = function(id) {
        var promise,
          _this = this;
        promise = this.Api.sendDelete('/twitter_accounts/' + id).then(function() {
          return _this.removeListModelById(id);
        });
        return promise;
      };

      /*
      			 # Get the form mapper
      			 #
      			 # @return {TwitterAccountEditFormMapper}
      */


      Admin_TwitterAccounts_DataService_TwitterAccounts.prototype.getFormMapper = function() {
        if (this.formMapper) {
          return this.formMapper;
        }
        this.formMapper = new TwitterAccountEditFormMapper();
        return this.formMapper;
      };

      /*
        	# Get all data needed for the edit page
        	#
        	# @param {Integer} id twitter_account id
        	# @return {promise}
      */


      Admin_TwitterAccounts_DataService_TwitterAccounts.prototype.loadEditTwitterAccountData = function(id) {
        var data, deferred,
          _this = this;
        deferred = this.$q.defer();
        if (id) {
          this.Api.sendGet('/twitter_accounts/' + id).then(function(result) {
            var data;
            data = {};
            data.twitter_account = result.data.twitter_account;
            data.all_agents = result.data.twitter_account.all_agents;
            data.form = _this.getFormMapper().getFormFromModel(data);
            return deferred.resolve(data);
          }, function() {
            return deferred.reject();
          });
        } else {
          data = {};
          data.twitter_account = {
            id: null,
            verified: false,
            user: {
              profile_image_url: '',
              name: '',
              screen_name: '',
              agents: {}
            }
          };
          data.form = this.getFormMapper().getFormFromModel(data);
          deferred.resolve(data);
        }
        return deferred.promise;
      };

      /*
        	# Saves a form model and merges model with list data
        	#
        	# @param {Object} model twitter_account model
       				# @param {Object} formModel  The model representing the form
        	# @return {promise}
      */


      Admin_TwitterAccounts_DataService_TwitterAccounts.prototype.saveFormModel = function(model, formModel) {
        var mapper, postData, promise,
          _this = this;
        mapper = this.getFormMapper();
        postData = mapper.getPostDataFromForm(formModel);
        if (model.id) {
          promise = this.Api.sendPostJson('/twitter_accounts/' + model.id, {
            twitter_account: postData
          });
        } else {
          promise = this.Api.sendPutJson('/twitter_accounts', {
            twitter_account: postData
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

      return Admin_TwitterAccounts_DataService_TwitterAccounts;

    })(BaseListEdit);
  });

}).call(this);

/*
//@ sourceMappingURL=TwitterAccounts.js.map
*/