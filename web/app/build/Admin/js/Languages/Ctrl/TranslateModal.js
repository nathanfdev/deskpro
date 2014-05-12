(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['angular', 'Admin/Main/Ctrl/Base'], function(angular, Admin_Ctrl_Base) {
    var Admin_Languages_Ctrl_TranslateModal;
    Admin_Languages_Ctrl_TranslateModal = (function(_super) {
      __extends(Admin_Languages_Ctrl_TranslateModal, _super);

      function Admin_Languages_Ctrl_TranslateModal() {
        return Admin_Languages_Ctrl_TranslateModal.__super__.constructor.apply(this, arguments);
      }

      Admin_Languages_Ctrl_TranslateModal.CTRL_ID = 'Admin_Languages_Ctrl_TranslateModal';

      Admin_Languages_Ctrl_TranslateModal.CTRL_AS = 'TranslateModal';

      Admin_Languages_Ctrl_TranslateModal.DEPS = ['$timeout', '$modalInstance', 'phraseId', 'editorOptions'];

      Admin_Languages_Ctrl_TranslateModal.prototype.init = function() {
        this.phrase_map = {};
        this.active_lang = null;
        this.active_trans = null;
        this.hasPendingPromise = false;
        this.options = this.editorOptions;
        this.$scope.dismiss = (function(_this) {
          return function() {
            return _this.$modalInstance.dismiss('cancel');
          };
        })(this);
        this.$scope.save = (function(_this) {
          return function() {
            if (_this.active_lang) {
              _this.phrase_map[_this.active_lang] = _this.active_trans;
            }
            return _this.savePhrases().then(function() {
              return _this.$modalInstance.close();
            });
          };
        })(this);
        this.$scope.$watch((function(_this) {
          return function() {
            return _this.active_lang;
          };
        })(this), (function(_this) {
          return function(newLangId, oldLangId) {
            if (!oldLangId) {
              return;
            }
            _this.phrase_map[oldLangId] = _this.active_trans;
            if (_this.phrase_map[newLangId]) {
              return _this.active_trans = _this.phrase_map[newLangId];
            } else {
              return _this.active_trans = '';
            }
          };
        })(this));
        return this.$scope.$watch((function(_this) {
          return function() {
            return _this.active_trans;
          };
        })(this), (function(_this) {
          return function() {
            if (!_this.active_lang) {
              return;
            }
            return _this.phrase_map[_this.active_lang] = _this.active_trans;
          };
        })(this));
      };

      Admin_Languages_Ctrl_TranslateModal.prototype.initialLoad = function() {
        var p;
        p = this.Api.sendDataGet({
          langs: '/langs',
          lang_phrases: '/langs/phrases/' + this.phraseId
        }).success((function(_this) {
          return function(data) {
            var first, l, lang_id, phrase, _i, _j, _len, _len1, _ref, _ref1;
            _this.ctrl_is_loading = false;
            if (_this.options.exclude_own || _this.options.exclude_default) {
              _this.langs = [];
              _ref = data.langs.languages;
              for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                l = _ref[_i];
                if (_this.options.exclude_own && DP_PERSON_LANG_ID === l.id) {
                  continue;
                }
                if (_this.options.exclude_default && l.id === data.langs.default_lang_id) {
                  continue;
                }
                _this.langs.push(l);
              }
            } else {
              _this.langs = data.langs.languages;
            }
            first = null;
            _ref1 = data.lang_phrases.lang_phrases;
            for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
              phrase = _ref1[_j];
              if (!first) {
                first = phrase;
              }
              lang_id = phrase.language.id;
              _this.phrase_map[lang_id] = phrase.phrase;
            }
            if (first) {
              _this.active_lang = first.language.id;
              return _this.active_trans = first.phrase;
            } else {
              _this.active_lang = _this.langs[0].id;
              return _this.active_trans = null;
            }
          };
        })(this));
        return p;
      };

      Admin_Languages_Ctrl_TranslateModal.prototype.savePhrases = function() {
        var api, defer, p, phrase_id, phrase_map, ret, saveInfo;
        phrase_map = angular.copy(this.phrase_map);
        phrase_id = this.phraseId;
        api = this.Api;
        saveInfo = {
          phrase_id: phrase_id,
          phrase_map: phrase_map,
          saver: function(phrase_id, phrase_map) {
            var k, postData, v;
            postData = {
              'lang_phrases': []
            };
            for (k in phrase_map) {
              if (!__hasProp.call(phrase_map, k)) continue;
              v = phrase_map[k];
              postData.lang_phrases.push({
                phrase: v || '',
                language_id: k
              });
            }
            return api.sendPostJson('/langs/phrases/' + phrase_id, postData);
          }
        };
        saveInfo.save = function() {
          return saveInfo.saver(saveInfo.phrase_id, saveInfo.phrase_map);
        };
        if (this.editorOptions.saveHandler) {
          ret = this.editorOptions.saveHandler(saveInfo.phrase_id, saveInfo.phrase_map, saveInfo.saver);
        } else {
          ret = saveInfo.save();
        }
        if (ret.then) {
          p = ret;
          this.$scope.is_loading = true;
          ret.then((function(_this) {
            return function() {
              return _this.$scope.is_loading = false;
            };
          })(this));
        } else {
          defer = this.$q.defer();
          defer.resolve();
          p = defer.promise;
        }
        return p;
      };

      return Admin_Languages_Ctrl_TranslateModal;

    })(Admin_Ctrl_Base);
    return Admin_Languages_Ctrl_TranslateModal.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=TranslateModal.js.map
