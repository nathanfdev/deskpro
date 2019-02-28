define(function() {
  const _teams = [];
  const _maps = { ids: {} };
  class DeskPRO_Service_AgentTeam {

    constructor($http, $q) {
      this.$http = $http;
      this.$q = $q;
    }


    _load() {
      const d = this.$q.defer();

      if (_teams.length) {
        d.resolve(_teams);
      } else {
        this.$http.get(`${BASE_URL}agent/agent_team`, { params: {} })
        .success((data, status, headers, config) => {
          data = data || [];
          data.map((team) => {
            const i = _teams.length;
            _teams.push(team);
            return _maps.ids[team.id] = i;
          });
          return d.resolve(_teams);
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
        for (const team of Array.from(_teams)) {
          if (res.length >= limit) { break; }
          if (!term.length || (team.name.toLowerCase().indexOf(term) > -1)) { res.push(team); }
        }

        return d.resolve(res);
      });

      return d.promise;
    }
  }
  return DeskPRO_Service_AgentTeam;
});

