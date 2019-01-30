// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/Ctrl/Base',
  'Admin/Languages/PhraseSaver'
], function(
  Admin_Ctrl_Base,
  PhraseSaver
) {
  class Admin_Languages_Ctrl_PhraseGroup extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_Languages_Ctrl_PhraseGroup';
      this.CTRL_AS = 'EditCtrl';
    }

    init() {
      this.langId  = this.$stateParams.id.replace(/^phrases\-/, '');
      return this.groupId = this.$stateParams.groupId;
    }

    initialLoad() {
      const promise = this.Api.sendDataGet({
        phrase_info: `/langs/${this.langId}/${this.groupId}`
      }).then(result => {
        return this.phrases = result.data.phrase_info.phrases;
      });
      return promise;
    }

    doSave() {
      this.startSpinner('saving');
      const saver = new PhraseSaver(this.Api, this.$q);
      return saver.savePhrases(this.langId, this.phrases).then(() => {
        return this.stopSpinner('saving');
      });
    }

    /*
      * Opens new phrase modal
    */
    openNewPhrase() {
      const { langId }  = this;
      const { groupId } = this;
      const phrasesCollection = this.phrases;

      return this.$modal.open({
        templateUrl: this.getTemplatePath('Languages/modal-new-phrase.html'),
        controller: [ '$modalInstance', '$scope', 'Api', '$state', function($modalInstance, $scope, Api, $state) {

          $scope.phrase = {name: '', phrase: ''};

          $scope.$watch('phrase.name', function() {
            $scope.phrase.name = $scope.phrase.name.toLowerCase();
            $scope.phrase.name = $scope.phrase.name.replace(/\s/g, '-');
            return $scope.phrase.name = $scope.phrase.name.replace(/[^a-z0-9\.\-_]/g, '');
          });

          $scope.dismiss = () => $modalInstance.dismiss('cancel');

          return $scope.save = function() {
            $scope.is_loading = true;

            const postData = {
              phrases: [{ name: `custom.${$scope.phrase.name}`, phrase: $scope.phrase.phrase}]
            };
            return Api.sendPostJson(`/langs/${langId}/phrases`, postData).then(function() {
              $modalInstance.close();
              return $state.go('setup.phrases_go_viewgroup', {path: `phrases-go-${langId}-${groupId}`});
            });
          };
        }
        ],
      }).result.then( newPhrase => {
        if (!newPhrase) { return; }
      });
    }
  }
  Admin_Languages_Ctrl_PhraseGroup.initClass();

  return Admin_Languages_Ctrl_PhraseGroup.EXPORT_CTRL();
});