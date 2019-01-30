// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
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
  class Admin_Languages_Ctrl_PhraseResGroup extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_Languages_Ctrl_PhraseResGroup';
      this.CTRL_AS = 'EditCtrl';
    }

    init() {
      this.langId  = this.$stateParams.id.replace(/^phrases\-/, '');
      return this.groupId = this.$stateParams.groupId.replace(/^res\-/, '');
    }

    initialLoad() {
      const promise = this.Api.sendDataGet({
        phrase_info: `/langs/${this.langId}/${this.groupId}`
      }).then(result => {
        let phrase;
        const { phrases } = result.data.phrase_info;

        for (let p of Array.from(phrases)) {
          if (p.depth) {
            p.depth_items = Array.from(Array(p.depth).keys());
          }
        }

        // Add header item before elements for same field
        // For example if we have Title phrase element and Description phrase element for one field
        //  - then add header element to display before them
        const phrasesWithHeaders = [];
        let prevPhrase = null;
        let fieldPhrasesCnt = 0;
        while ((phrase = phrases.pop())) {
          if (prevPhrase) {
            if (prevPhrase.type_id === phrase.type_id) {
              ++fieldPhrasesCnt;
            } else {
              if (fieldPhrasesCnt) {
                phrasesWithHeaders.push({
                  type: '_header',
                  title: prevPhrase.default
                });
              }
              fieldPhrasesCnt = 0;
            }
          }
          phrasesWithHeaders.push(phrase);
          prevPhrase = phrase;
        }
        if (prevPhrase && fieldPhrasesCnt) {
          phrasesWithHeaders.push({
            type: '_header',
            title: prevPhrase.default
          });
        }

        return this.phrases = phrasesWithHeaders.reverse();
      });
      return promise;
    }

    doSave() {
      this.startSpinner('saving');
      const saver = new PhraseSaver(this.Api, this.$q);
      return saver.savePhrases(this.langId, this.phrases.filter(x => x.type !== '_header')).then(() => {
        return this.stopSpinner('saving');
      });
    }
  }
  Admin_Languages_Ctrl_PhraseResGroup.initClass();

  return Admin_Languages_Ctrl_PhraseResGroup.EXPORT_CTRL();
});