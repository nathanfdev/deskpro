// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(function() {
  class TemplateLoader {
    constructor(loadUrl, $http, $q) {
      this.loadUrl = loadUrl;
      this.$http = $http;
      this.$q = $q;
    }

    setBust(val) {
      return this.bust = val;
    }

    getLoadUrl(views) {
      let qs = [];

      for (let t of Array.from(views)) {
        qs.push(`views[]=${encodeURIComponent(t)}`);
      }

      if (this.bust) {
        qs.push(this.bust);
      }

      qs = qs.join('&');

      return this.loadUrl + '?' + qs;
    }

    load(views) {
      const d = this.$q.defer();

      this.$http({
        method: 'GET',
        url: this.getLoadUrl(views)
      }).success( data => d.resolve(data)
      , (data, status) => d.reject(data, status));

      return d.promise;
    }
  }
  return TemplateLoader;
});