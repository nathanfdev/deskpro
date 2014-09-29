(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/Languages/PhraseSaver'], function(Admin_Ctrl_Base, PhraseSaver) {
    var Admin_Languages_Ctrl_PhraseGroup;
    Admin_Languages_Ctrl_PhraseGroup = (function(_super) {
      __extends(Admin_Languages_Ctrl_PhraseGroup, _super);

      function Admin_Languages_Ctrl_PhraseGroup() {
        return Admin_Languages_Ctrl_PhraseGroup.__super__.constructor.apply(this, arguments);
      }

      Admin_Languages_Ctrl_PhraseGroup.CTRL_ID = 'Admin_Languages_Ctrl_PhraseGroup';

      Admin_Languages_Ctrl_PhraseGroup.CTRL_AS = 'EditCtrl';

      Admin_Languages_Ctrl_PhraseGroup.prototype.init = function() {
        this.langId = this.$stateParams.id.replace(/^phrases\-/, '');
        return this.groupId = this.$stateParams.groupId;
      };

      Admin_Languages_Ctrl_PhraseGroup.prototype.initialLoad = function() {
        var promise;
        promise = this.Api.sendDataGet({
          phrase_info: "/langs/" + this.langId + "/" + this.groupId
        }).then((function(_this) {
          return function(result) {
            return _this.phrases = result.data.phrase_info.phrases;
          };
        })(this));
        return promise;
      };

      Admin_Languages_Ctrl_PhraseGroup.prototype.doSave = function() {
        var saver;
        this.startSpinner('saving');
        saver = new PhraseSaver(this.Api, this.$q);
        return saver.savePhrases(this.langId, this.phrases).then((function(_this) {
          return function() {
            return _this.stopSpinner('saving');
          };
        })(this));
      };


      /*
        	 * Opens new phrase modal
       */

      Admin_Languages_Ctrl_PhraseGroup.prototype.openNewPhrase = function() {
        var groupId, langId, phrasesCollection;
        langId = this.langId;
        groupId = this.groupId;
        phrasesCollection = this.phrases;
        return this.$modal.open({
          templateUrl: this.getTemplatePath('Languages/modal-new-phrase.html'),
          controller: [
            '$modalInstance', '$scope', 'Api', '$state', function($modalInstance, $scope, Api, $state) {
              $scope.phrase = {
                name: '',
                phrase: ''
              };
              $scope.$watch('phrase.name', function() {
                $scope.phrase.name = $scope.phrase.name.toLowerCase();
                $scope.phrase.name = $scope.phrase.name.replace(/\s/g, '-');
                return $scope.phrase.name = $scope.phrase.name.replace(/[^a-z0-9\.\-_]/g, '');
              });
              $scope.dismiss = function() {
                return $modalInstance.dismiss('cancel');
              };
              return $scope.save = function() {
                var postData;
                $scope.is_loading = true;
                postData = {
                  phrases: [
                    {
                      name: 'custom.' + $scope.phrase.name,
                      phrase: $scope.phrase.phrase
                    }
                  ]
                };
                return Api.sendPostJson("/langs/" + langId + "/phrases", postData).then(function() {
                  $modalInstance.close();
                  return $state.go('setup.phrases_go_viewgroup', {
                    path: 'phrases-go-' + langId + '-' + groupId
                  });
                });
              };
            }
          ]
        }).result.then((function(_this) {
          return function(newPhrase) {
            if (!newPhrase) {

            }
          };
        })(this));
      };

      return Admin_Languages_Ctrl_PhraseGroup;

    })(Admin_Ctrl_Base);
    return Admin_Languages_Ctrl_PhraseGroup.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=PhraseGroup.js.map
