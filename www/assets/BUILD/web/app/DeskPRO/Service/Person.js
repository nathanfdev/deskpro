define(function() {
  let _persons = [];
  let _maps = {
    ids: {},
    agents: {},
    disabled: {},
    deleted: {},
    online: {}
  };
  class DeskPRO_Service_Person {
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
  }
  return DeskPRO_Service_Person;
});



