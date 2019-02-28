define(['angular'], function(angular) {
  const updateColorForLabel = null;
  let loadDefinitions = null;
  class DeskPRO_Service_LabelDefinition {
    constructor($q, definitionsPromise) {
      this.all = this.all.bind(this);
      this.$q = $q;
      let loadPromise = null;
      this.definitions = {
        tickets:       {},
        people:        {},
        organizations: {},
        news:          {},
        kb:            {},
        feedback:      {},
        downloads:     {},
        chat:          {}
      };

      loadDefinitions = () => {
        if (loadPromise) { return loadPromise; }
        const d = this.$q.defer();

        definitionsPromise.then((data) => {
          for (const def of Array.from(data.data)) {
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
  }
  return DeskPRO_Service_LabelDefinition;
});
