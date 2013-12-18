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
        this.$scope.isInstalled = function(pack) {
          return pack.is_installed;
        };
        return this.$scope.notInstalled = function(pack) {
          return !pack.is_installed;
        };
      };

      Admin_Languages_Ctrl_List.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.Api.sendGet('/langs').then(function(result) {
          _this.packs = result.data.packs;
          _this.installedPacks = [];
          _this.availablePacks = [];
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
          pack.flag_image = DP_ASSET_URL + '/images/flags/' + pack.flag;
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
        promise = this.Api.sendGet("/langs/" + pack_id + "/install").then(function(result) {
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
        promise = this.Api.sendGet("/langs/" + id + "/uninstall").then(function(result) {
          var pack;
          pack = _this._getPackByPackId(result.data.old_pack_id);
          pack.is_installed = false;
          return _this.resortPacks();
        });
        return promise;
      };

      return Admin_Languages_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_Languages_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=List.js.map
*/