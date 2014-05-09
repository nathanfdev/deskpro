(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Languages_Ctrl_PhraseList;
    Admin_Languages_Ctrl_PhraseList = (function(_super) {
      __extends(Admin_Languages_Ctrl_PhraseList, _super);

      function Admin_Languages_Ctrl_PhraseList() {
        return Admin_Languages_Ctrl_PhraseList.__super__.constructor.apply(this, arguments);
      }

      Admin_Languages_Ctrl_PhraseList.CTRL_ID = 'Admin_Languages_Ctrl_PhraseList';

      Admin_Languages_Ctrl_PhraseList.CTRL_AS = 'ListCtrl';

      Admin_Languages_Ctrl_PhraseList.prototype.init = function() {
        return this.id = this.$stateParams.id.replace(/^phrases\-/, '');
      };

      Admin_Languages_Ctrl_PhraseList.prototype.initialLoad = function() {
        var promise;
        promise = this.Api.sendDataGet({
          lang_info: "/langs/" + this.id,
          phrase_groups: "/langs/phrases-groups"
        }).then((function(_this) {
          return function(result) {
            if (!result.data.lang_info.language) {
              _this.$state.go('setup.languages.install', {
                id: "install-" + _this.id
              });
              return;
            }
            _this.pack = result.data.lang_info.pack;
            _this.lang = result.data.lang_info.language;
            return _this.phraseGroups = result.data.phrase_groups.phrase_groups;
          };
        })(this));
        return promise;
      };

      return Admin_Languages_Ctrl_PhraseList;

    })(Admin_Ctrl_Base);
    return Admin_Languages_Ctrl_PhraseList.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=PhraseList.js.map
