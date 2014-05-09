(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/Languages/PhraseSaver'], function(Admin_Ctrl_Base, PhraseSaver) {
    var Admin_Languages_Ctrl_PhraseResGroup;
    Admin_Languages_Ctrl_PhraseResGroup = (function(_super) {
      __extends(Admin_Languages_Ctrl_PhraseResGroup, _super);

      function Admin_Languages_Ctrl_PhraseResGroup() {
        return Admin_Languages_Ctrl_PhraseResGroup.__super__.constructor.apply(this, arguments);
      }

      Admin_Languages_Ctrl_PhraseResGroup.CTRL_ID = 'Admin_Languages_Ctrl_PhraseResGroup';

      Admin_Languages_Ctrl_PhraseResGroup.CTRL_AS = 'EditCtrl';

      Admin_Languages_Ctrl_PhraseResGroup.prototype.init = function() {
        this.langId = this.$stateParams.id.replace(/^phrases\-/, '');
        return this.groupId = this.$stateParams.groupId.replace(/^res\-/, '');
      };

      Admin_Languages_Ctrl_PhraseResGroup.prototype.initialLoad = function() {
        var promise;
        promise = this.Api.sendDataGet({
          phrase_info: "/langs/" + this.langId + "/" + this.groupId
        }).then((function(_this) {
          return function(result) {
            var p, _i, _len, _ref, _results;
            _this.phrases = result.data.phrase_info.phrases;
            _ref = _this.phrases;
            _results = [];
            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
              p = _ref[_i];
              if (p.depth) {
                _results.push(p.depth_items = new Array(p.depth));
              } else {
                _results.push(void 0);
              }
            }
            return _results;
          };
        })(this));
        return promise;
      };

      Admin_Languages_Ctrl_PhraseResGroup.prototype.doSave = function() {
        var saver;
        this.startSpinner('saving');
        saver = new PhraseSaver(this.Api, this.$q);
        return saver.savePhrases(this.langId, this.phrases).then((function(_this) {
          return function() {
            return _this.stopSpinner('saving');
          };
        })(this));
      };

      return Admin_Languages_Ctrl_PhraseResGroup;

    })(Admin_Ctrl_Base);
    return Admin_Languages_Ctrl_PhraseResGroup.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=PhraseResGroup.js.map
