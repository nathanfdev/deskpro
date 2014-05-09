(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit', 'Admin/Banning/IpBanEditFormMapper', 'Admin/Banning/EmailBanEditFormMapper'], function(BaseListEdit, IpBanEditFormMapper, EmailBanEditFormMapper) {
    var Bans;
    return Bans = (function(_super) {
      __extends(Bans, _super);

      function Bans() {
        return Bans.__super__.constructor.apply(this, arguments);
      }

      Bans.$inject = ['Api', '$q'];

      Bans.type = 'ip';


      /*
       	 *
       */

      Bans.prototype.init = function() {
        this.setSubLists(['ip_bans', 'email_bans']);
        return this.search_phrase = {
          ip_ban: '',
          email_ban: ''
        };
      };


      /*
       	 *
       */

      Bans.prototype._doLoadList = function() {
        var deferred;
        deferred = this.$q.defer();
        this.Api.sendGet('/banning').success((function(_this) {
          return function(data) {
            var models;
            models = data.bans;
            return deferred.resolve(models);
          };
        })(this), function(data, status, headers, config) {
          return deferred.reject();
        });
        return deferred.promise;
      };


      /*
      		 *
       */

      Bans.prototype._doRefreshList = function() {
        var deferred;
        deferred = this.$q.defer();
        this.Api.sendGet('/banning', {
          ip_ban_page: this.pagination.ip_bans.page,
          email_ban_page: this.pagination.email_bans.page,
          ip_ban_search_phrase: this.search_phrase.ip_ban,
          email_ban_search_phrase: this.search_phrase.email_ban
        }).success((function(_this) {
          return function(data) {
            var models;
            models = data.bans;
            return deferred.resolve(models);
          };
        })(this), function(data, status, headers, config) {
          return deferred.reject();
        });
        return deferred.promise;
      };


      /*
       	 * Returns object representing the search string defined via user UI
       */

      Bans.prototype.getSearchPhrase = function() {
        return this.search_phrase;
      };


      /*
       	 * Sets type of ban that is used for create / update / delete operations
      		 *
       	 * @param {string} type
       */

      Bans.prototype.setType = function(type) {
        this.type = type;
        return this.idProp = 'banned_' + this.type;
      };


      /*
      		 * Get the form mapper
      		 *
      		 * @return {IpBanEditFormMapper|EmailBanEditFormMapper}
       */

      Bans.prototype.getFormMapper = function() {
        if (this.type === 'ip') {
          this.formMapper = new IpBanEditFormMapper();
        }
        if (this.type === 'email') {
          this.formMapper = new EmailBanEditFormMapper();
        }
        return this.formMapper;
      };


      /*
       * Remove a model
       *
       * @param {Integer} id
       * @return {promise}
       */

      Bans.prototype.deleteBanById = function(id) {
        var promise;
        promise = this.Api.sendDelete('/banning_' + this.type + '/' + id).success((function(_this) {
          return function() {
            return _this.removeListModelById(id);
          };
        })(this));
        return promise;
      };


      /*
       * Get all data needed for the edit page
       *
       * @param {String} id
       * @return {promise}
       */

      Bans.prototype.loadEditBanData = function(id) {
        var data, deferred;
        deferred = this.$q.defer();
        if (id) {
          this.Api.sendGet('/banning_' + this.type + '/' + id).then((function(_this) {
            return function(result) {
              var data;
              data = {};
              data[_this.type + '_ban'] = result.data[_this.type + '_ban'];
              data[_this.type + '_ban'].old_id = result.data[_this.type + '_ban'][_this.idProp];
              data.form = _this.getFormMapper().getFormFromModel(data);
              return deferred.resolve(data);
            };
          })(this), function() {
            return deferred.reject();
          });
        } else {
          data = {};
          data[this.type + '_ban'] = {};
          data.form = this.getFormMapper().getFormFromModel(data);
          deferred.resolve(data);
        }
        return deferred.promise;
      };


      /*
       * Saves a form model and merges model with list data
       *
       * @param {Object} model
       	 * @param {Object} formModel  The model representing the form
       * @return {promise}
       */

      Bans.prototype.saveFormModel = function(model, formModel) {
        var mapper, postData, promise, sendData;
        mapper = this.getFormMapper();
        postData = mapper.getPostDataFromForm(formModel);
        sendData = {};
        sendData[this.type + '_ban'] = postData;
        if (model['banned_' + this.type]) {
          promise = this.Api.sendPostJson('/banning_' + this.type + '/' + model['banned_' + this.type], sendData).success((function(_this) {
            return function(data) {
              return model['banned_' + _this.type] = data['banned_' + _this.type];
            };
          })(this));
        } else {
          promise = this.Api.sendPutJson('/banning_' + this.type, sendData).success((function(_this) {
            return function(data) {
              return model['banned_' + _this.type] = data['banned_' + _this.type];
            };
          })(this));
        }
        promise.success((function(_this) {
          return function() {
            mapper.applyFormToModel(model, formModel);
            return _this.mergeDataModel(model, null, _this.type + '_bans');
          };
        })(this));
        return promise;
      };

      return Bans;

    })(BaseListEdit);
  });

}).call(this);

//# sourceMappingURL=Bans.js.map
