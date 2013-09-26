(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Languages_Ctrl_TranslateModal, _ref;
    Admin_Languages_Ctrl_TranslateModal = (function(_super) {
      __extends(Admin_Languages_Ctrl_TranslateModal, _super);

      function Admin_Languages_Ctrl_TranslateModal() {
        _ref = Admin_Languages_Ctrl_TranslateModal.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_Languages_Ctrl_TranslateModal.CTRL_ID = 'Admin_Languages_Ctrl_TranslateModal';

      Admin_Languages_Ctrl_TranslateModal.CTRL_TYPE = 'modal';

      Admin_Languages_Ctrl_TranslateModal.CTRL_AS = 'TranslateModal';

      Admin_Languages_Ctrl_TranslateModal.DEPS = ['$timeout', '$modalInstance', 'phraseId'];

      Admin_Languages_Ctrl_TranslateModal.prototype.init = function() {
        var _this = this;
        this.phrase_map = {};
        this.active_lang = null;
        this.active_trans = null;
        this.$scope.dismiss = function() {
          return _this.$modalInstance.dismiss('cancel');
        };
        this.$scope.save = function() {
          if (_this.active_lang) {
            _this.phrase_map[_this.active_lang] = _this.active_trans;
          }
          _this.savePhrases();
          return _this.$modalInstance.close();
        };
        this.$scope.$watch(function() {
          return _this.active_lang;
        }, function(newLangId, oldLangId) {
          if (!oldLangId) {
            return;
          }
          _this.phrase_map[oldLangId] = _this.active_trans;
          if (_this.phrase_map[newLangId]) {
            return _this.active_trans = _this.phrase_map[newLangId];
          } else {
            return _this.active_trans = '';
          }
        });
        return this.$scope.$watch(function() {
          return _this.active_trans;
        }, function() {
          if (!_this.active_lang) {
            return;
          }
          return _this.phrase_map[_this.active_lang] = _this.active_trans;
        });
      };

      Admin_Languages_Ctrl_TranslateModal.prototype.initialLoad = function() {
        var p,
          _this = this;
        p = this.Api.sendDataGet(['/langs', '/langs/phrases/' + this.phraseId]).success(function(data) {
          var first, lang_id, phrase, _i, _len, _ref1;
          _this.langs = data.api_langs.languages;
          first = null;
          _ref1 = data.api_langs_getphrase.lang_phrases;
          for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
            phrase = _ref1[_i];
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
        });
        return p;
      };

      Admin_Languages_Ctrl_TranslateModal.prototype.savePhrases = function() {
        var k, postData, promise, v, _ref1;
        postData = {
          'lang_phrases': []
        };
        _ref1 = this.phrase_map;
        for (k in _ref1) {
          if (!__hasProp.call(_ref1, k)) continue;
          v = _ref1[k];
          postData.lang_phrases.push({
            phrase: v || '',
            language_id: k
          });
        }
        promise = this.Api.sendPostJson('/langs/phrases/' + this.phraseId, postData);
        return promise;
      };

      return Admin_Languages_Ctrl_TranslateModal;

    })(Admin_Ctrl_Base);
    return Admin_Languages_Ctrl_TranslateModal.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=TranslateModal.js.map
*/