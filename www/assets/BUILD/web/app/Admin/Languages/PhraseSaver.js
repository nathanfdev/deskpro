// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['DeskPRO/Util/Strings'], function(Strings) {
  let PhraseSaver;
  return (PhraseSaver = class PhraseSaver {
    constructor(Api, $q) {
      this.Api = Api;
      this.$q = $q;
    }

    /*
      * Saves an array of phrases
      *
      * @param {Array} phrases
      * @return {promise}
    */
    savePhrases(langId, phrases) {
      const savePhrases = [];

      for (let p of Array.from(phrases)) {
        let setVal = p.set;

        if (setVal) {
          p.set = Strings.trim(p.set);
        }

        if (!setVal || (p.set === "") || (p.set === p.lang_default)) {
          setVal = null;
        }

        if (setVal === null) {
          p.set = p.lang_default;
        }

        savePhrases.push({
          name: p.id,
          phrase: setVal
        });
      }

      const deferred = this.$q.defer();
      if (!savePhrases.length) {
        deferred.resolve([]);
        return deferred.promise;
      }

      phrases = [];
      for (let n of Array.from(savePhrases)) {
        if ('user.general.helpdesk_by' !== n.name) { phrases.push(n); }
      }

      this.Api.sendPostJson(`/langs/${langId}/phrases`, {
        phrases
      }).success(() => deferred.resolve(savePhrases)).error(() => deferred.reject());

      return deferred.promise;
    }
  });
});