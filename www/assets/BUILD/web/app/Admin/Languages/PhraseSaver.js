define(['DeskPRO/Util/Strings'], (Strings) => {
  class PhraseSaver {
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

      for (const p of Array.from(phrases)) {
        let setVal = p.set;

        if (setVal) {
          p.set = Strings.trim(p.set);
        }

        if (!setVal || (p.set === '') || (p.set === p.lang_default)) {
          setVal = null;
        }

        if (setVal === null) {
          p.set = p.lang_default;
        }

        savePhrases.push({
          name:   p.id,
          phrase: setVal
        });
      }

      const deferred = this.$q.defer();
      if (!savePhrases.length) {
        deferred.resolve([]);
        return deferred.promise;
      }

      phrases = [];
      for (const n of Array.from(savePhrases)) {
        if (n.name !== 'user.general.helpdesk_by') { phrases.push(n); }
      }

      this.Api.sendPostJson(`/langs/${langId}/phrases`, {
        phrases
      }).success(() => deferred.resolve(savePhrases)).error(() => deferred.reject());

      return deferred.promise;
    }
  }
  return PhraseSaver;
});
