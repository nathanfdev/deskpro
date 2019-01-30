// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['angular'], function(angular) {
  let DeskPRO_Service_LabelDefinition;
  return DeskPRO_Service_LabelDefinition = (function() {
    let loadDefinitions = undefined;
    let updateColorForLabel = undefined;
    DeskPRO_Service_LabelDefinition = class DeskPRO_Service_LabelDefinition {
      static initClass() {
        loadDefinitions = null;
        updateColorForLabel = null;
      }

      constructor($q, definitionsPromise) {
        this.all = this.all.bind(this);
        this.$q = $q;
        let loadPromise = null;
        this.definitions = {
          tickets: {},
          people: {},
          organizations: {},
          news: {},
          kb: {},
          feedback: {},
          downloads: {},
          chat: {}
        };

        loadDefinitions = () => {
          if (loadPromise) { return loadPromise; }
          const d = this.$q.defer();

          definitionsPromise.then(data => {

            for (let def of Array.from(data.data)) {
              if ((def.label == null) || (def.label_type == null)) { continue; }
              const label = def.label.toLowerCase();
              this.definitions[def.label_type] = this.definitions[def.label_type] || {};
              this.definitions[def.label_type][label] = angular.copy(def);
            }

            return d.resolve(this.definitions);
          });

          return loadPromise = d.promise;
        };
      }

      all(label_type) {
        const d = this.$q.defer();
        loadDefinitions().then(() => d.resolve(this.definitions[label_type]));
        return d.promise;
      }

      get(label_type, label) {
        const d = this.$q.defer();
        label = (label || '').toLowerCase();
        loadDefinitions().then(() => {
          let val;
          if (this.definitions[label_type]) {
            val = this.definitions[label_type][label];
          } else {
            val = null;
          }
          return d.resolve(val);
        });
        return d.promise;
      }

      update(_old, _new) {
        if (!_new.label || !_new.label_type) { return; }
        const label = _new.label.toLowerCase();

        if (_old) {
          delete this.definitions[_old.label_type][_old.label.toLowerCase()];
        }

        this.definitions[_new.label_type] = this.definitions[_new.label_type] || {};
        return this.definitions[_new.label_type][label] = _new;
      }

      remove(def) {
        if (!def.label || !def.label_type) { return; }
        if ((this.definitions[def.label_type] != null ? this.definitions[def.label_type][def.label.toLowerCase()] : undefined) != null) {
          return delete this.definitions[def.label_type][def.label.toLowerCase()];
        }
      }
    };
    DeskPRO_Service_LabelDefinition.initClass();
    return DeskPRO_Service_LabelDefinition;
  })();
});