(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Languages_Ctrl_List, _ref;
    Admin_Languages_Ctrl_List = (function(_super) {
      __extends(Admin_Languages_Ctrl_List, _super);

      function Admin_Languages_Ctrl_List() {
        _ref = Admin_Languages_Ctrl_List.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_Languages_Ctrl_List.CTRL_ID = 'Admin_Languages_Ctrl_List';

      Admin_Languages_Ctrl_List.CTRL_AS = 'ListCtrl';

      Admin_Languages_Ctrl_List.prototype.init = function() {
        var _this = this;
        this.$scope.isInstalled = function(pack) {
          return pack.is_installed;
        };
        this.$scope.notInstalled = function(pack) {
          return !pack.is_installed;
        };
        this.default_lang_id = null;
        return this.$scope.$watch('ListCtrl.default_lang_id', function(oldVal, newVal) {
          if (oldVal && newVal && parseInt(oldVal) !== parseInt(newVal)) {
            return _this.updateDefaultLang();
          }
        });
      };

      Admin_Languages_Ctrl_List.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.Api.sendGet('/langs').then(function(result) {
          _this.packs = result.data.packs;
          _this.installedPacks = [];
          _this.availablePacks = [];
          _this.default_lang_id = result.data.default_lang_id;
          return _this.resortPacks();
        });
        return promise;
      };

      /*
        	# Fetch a pack from its packId
        	#
        	# @param {String} packId
        	# @return {Object}
      */


      Admin_Languages_Ctrl_List.prototype._getPackByPackId = function(packId) {
        var pack, _i, _len, _ref1;
        _ref1 = this.packs;
        for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
          pack = _ref1[_i];
          if (pack.id === packId) {
            return pack;
          }
        }
        return null;
      };

      /*
      		# Resort packs into installed/available lists
      */


      Admin_Languages_Ctrl_List.prototype.resortPacks = function() {
        var pack, _i, _len, _ref1, _results;
        this.installedPacks = [];
        this.availablePacks = [];
        _ref1 = this.packs;
        _results = [];
        for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
          pack = _ref1[_i];
          pack.flag_image = DP_ASSET_URL + '/images/flags/' + pack.show_flag;
          if (pack.is_installed) {
            _results.push(this.installedPacks.push(pack));
          } else {
            _results.push(this.availablePacks.push(pack));
          }
        }
        return _results;
      };

      /*
        	# Install a language by pack_id
        	#
      		# @return promise
      */


      Admin_Languages_Ctrl_List.prototype.installLang = function(pack_id) {
        var promise,
          _this = this;
        promise = this.Api.sendPost("/langs/" + pack_id + "/install").then(function(result) {
          var pack;
          pack = _this._getPackByPackId(result.data.pack_id);
          pack.is_installed = true;
          return _this.resortPacks();
        });
        return promise;
      };

      /*
        	# Uninstall a language by language_id or pack_id
        	#
        	# @return promise
      */


      Admin_Languages_Ctrl_List.prototype.uninstallLang = function(id) {
        var promise,
          _this = this;
        promise = this.Api.sendPost("/langs/" + id + "/uninstall").then(function(result) {
          var pack;
          pack = _this._getPackByPackId(result.data.old_pack_id);
          pack.is_installed = false;
          return _this.resortPacks();
        });
        return promise;
      };

      /*
        	# Save a language
        	#
        	# @return promise
      */


      Admin_Languages_Ctrl_List.prototype.saveLanguage = function(id, details) {
        var pack, postData, promise,
          _this = this;
        pack = this._getPackByPackId(id);
        postData = {
          language: details
        };
        promise = this.Api.sendPostJson("/langs/" + id, postData).then(function(result) {
          pack.show_title = details.title;
          pack.locale = details.locale;
          pack.show_flag = details.flag_image;
          return _this.resortPacks();
        });
        return promise;
      };

      Admin_Languages_Ctrl_List.prototype.updateDefaultLang = function() {
        var _this = this;
        if (this.default_lang_id && this.hasLoaded()) {
          this.startSpinner('saving_default_lang');
          return this.Api.sendPost("/langs/" + this.default_lang_id + "/set-default").then(function() {
            return _this.stopSpinner('saving_default_lang');
          });
        }
      };

      return Admin_Languages_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_Languages_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=List.js.map
*/