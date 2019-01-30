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
  let DeskPRO_Service_AgentTeam;
  return DeskPRO_Service_AgentTeam = (function() {
    let _teams = undefined;
    let _maps = undefined;
    DeskPRO_Service_AgentTeam = class DeskPRO_Service_AgentTeam {
      static initClass() {
  
        _teams = [];
        _maps =
          {ids: {}};
      }



      constructor($http, $q) {
        this.$http = $http;
        this.$q = $q;
      }



      _load() {
        const d = this.$q.defer();

        if (_teams.length) {
          d.resolve(_teams);
        } else {
          this.$http.get(BASE_URL + 'agent/agent_team', {params: {}})
          .success((data, status, headers, config) => {

            data = data || [];
            data.map(team => {
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
          for (let team of Array.from(_teams)) {
            if (res.length >= limit) { break; }
            if (!term.length || (team.name.toLowerCase().indexOf(term) > -1)) { res.push(team); }
          }

          return d.resolve(res);
        });

        return d.promise;
      }
    };
    DeskPRO_Service_AgentTeam.initClass();
    return DeskPRO_Service_AgentTeam;
  })();
});



