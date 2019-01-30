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
define(function() {
  /*
   *
   */
  let DeskPRO_Service_Person;
  return DeskPRO_Service_Person = (function() {
    let _persons = undefined;
    let _maps = undefined;
    DeskPRO_Service_Person = class DeskPRO_Service_Person {
      static initClass() {
  
        _persons = [];
        _maps = {
          ids: {},
          agents: {},
          disabled: {},
          deleted: {},
          online: {}
        };
      }



      constructor($http, $q) {
        this.$http = $http;
        this.$q = $q;
      }



      _load() {
        const d = this.$q.defer();

        if (_persons.length) {
          d.resolve(_persons);
        } else {
          this.$http.get(BASE_URL + 'agent/person', {params: {is_agent: true}})
          .success((data, status, headers, config) => {

            data = data || [];
            data.map(person => {
              const i = _persons.length;
              _persons.push(person);
              _maps.ids[person.id] = i;
              return _maps.agents[person.id] = i;
            }); // we load only agents for now
            return d.resolve(_persons);
        }).error((data, status, headers, config) => {
            console.error(data, status);
            return d.reject();
          });
        }

        return d.promise;
      }




      find(term, limit) {
        limit = limit || 10;
        if ((term == null)) { term = ''; }
        term = term.toLowerCase();
        const d = this.$q.defer();

        this._load().then(() => {
          const res = [];
          for (let person of Array.from(_persons)) {
            if (res.length >= limit) { break; }
            if (!term.length || (person.display_name.toLowerCase().indexOf(term) > -1)) { res.push(person); }
          }

          return d.resolve(res);
        });

        return d.promise;
      }



      get(id) {
        const d = this.$q.defer();
        this._load().then(() => d.resolve(_persons[_maps.ids[id]] || null));
        return d.promise;
      }
    };
    DeskPRO_Service_Person.initClass();
    return DeskPRO_Service_Person;
  })();
});



