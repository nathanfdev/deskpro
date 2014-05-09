(function() {
  define(['DeskPRO/Util/Strings'], function(Strings) {
    var PhraseSaver;
    return PhraseSaver = (function() {
      function PhraseSaver(Api, $q) {
        this.Api = Api;
        this.$q = $q;
      }


      /*
        	 * Saves an array of phrases
        	 *
        	 * @param {Array} phrases
        	 * @return {promise}
       */

      PhraseSaver.prototype.savePhrases = function(langId, phrases) {
        var deferred, p, savePhrases, setVal, _i, _len;
        savePhrases = [];
        for (_i = 0, _len = phrases.length; _i < _len; _i++) {
          p = phrases[_i];
          setVal = p.set;
          if (setVal) {
            p.set = Strings.trim(p.set);
          }
          if (!setVal || p.set === "" || p.set === p.lang_default) {
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
        deferred = this.$q.defer();
        if (!savePhrases.length) {
          deferred.resolve([]);
          return deferred.promise;
        }
        this.Api.sendPostJson("/langs/" + langId + "/phrases", {
          phrases: savePhrases
        }).success(function() {
          return deferred.resolve(savePhrases);
        }).error(function() {
          return deferred.reject();
        });
        return deferred.promise;
      };

      return PhraseSaver;

    })();
  });

}).call(this);

//# sourceMappingURL=PhraseSaver.js.map
