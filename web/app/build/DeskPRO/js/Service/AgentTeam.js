(function() {
  define(function() {

    /*
      *
     */
    var DeskPRO_Service_AgentTeam;
    return DeskPRO_Service_AgentTeam = (function() {
      var _maps, _teams;

      _teams = [];

      _maps = {
        ids: {}
      };

      function DeskPRO_Service_AgentTeam($http, $q) {
        this.$http = $http;
        this.$q = $q;
      }

      DeskPRO_Service_AgentTeam.prototype._load = function() {
        var d;
        d = this.$q.defer();
        if (_teams.length) {
          d.resolve(_teams);
        } else {
          this.$http.get(BASE_URL + 'agent/agent_team', {
            params: {}
          }).success((function(_this) {
            return function(data, status, headers, config) {
              data = data || [];
              data.map(function(team) {
                var i;
                i = _teams.length;
                _teams.push(team);
                return _maps.ids[team.id] = i;
              });
              return d.resolve(_teams);
            };
          })(this)).error((function(_this) {
            return function(data, status, headers, config) {
              console.error(data, status);
              return d.reject();
            };
          })(this));
        }
        return d.promise;
      };

      DeskPRO_Service_AgentTeam.prototype.find = function(term, limit) {
        var d;
        limit = limit || 10;
        if (term == null) {
          term = '';
        }
        term = term.toLowerCase();
        d = this.$q.defer();
        this._load().then((function(_this) {
          return function() {
            var res, team, _i, _len;
            res = [];
            for (_i = 0, _len = _teams.length; _i < _len; _i++) {
              team = _teams[_i];
              if (res.length >= limit) {
                break;
              }
              if (!term.length || team.name.toLowerCase().indexOf(term) > -1) {
                res.push(team);
              }
            }
            return d.resolve(res);
          };
        })(this));
        return d.promise;
      };

      return DeskPRO_Service_AgentTeam;

    })();
  });

}).call(this);

//# sourceMappingURL=AgentTeam.js.map
