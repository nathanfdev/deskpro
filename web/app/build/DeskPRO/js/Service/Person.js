(function() {
  define(function() {

    /*
      *
     */
    var DeskPRO_Service_Person;
    return DeskPRO_Service_Person = (function() {
      var _maps, _persons;

      _persons = [];

      _maps = {
        ids: {},
        agents: {},
        disabled: {},
        deleted: {},
        online: {}
      };

      function DeskPRO_Service_Person($http, $q) {
        this.$http = $http;
        this.$q = $q;
      }

      DeskPRO_Service_Person.prototype._load = function() {
        var d;
        d = this.$q.defer();
        if (_persons.length) {
          d.resolve(_persons);
        } else {
          this.$http.get(BASE_URL + 'agent/person', {
            params: {
              is_agent: true
            }
          }).success((function(_this) {
            return function(data, status, headers, config) {
              data = data || [];
              data.map(function(person) {
                var i;
                i = _persons.length;
                _persons.push(person);
                _maps.ids[person.id] = i;
                return _maps.agents[person.id] = i;
              });
              return d.resolve(_persons);
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

      DeskPRO_Service_Person.prototype.find = function(term, limit) {
        var d;
        limit = limit || 10;
        if (term == null) {
          term = '';
        }
        term = term.toLowerCase();
        d = this.$q.defer();
        this._load().then((function(_this) {
          return function() {
            var person, res, _i, _len;
            res = [];
            for (_i = 0, _len = _persons.length; _i < _len; _i++) {
              person = _persons[_i];
              if (res.length >= limit) {
                break;
              }
              if (!term.length || person.display_name.toLowerCase().indexOf(term) > -1) {
                res.push(person);
              }
            }
            return d.resolve(res);
          };
        })(this));
        return d.promise;
      };

      DeskPRO_Service_Person.prototype.get = function(id) {
        var d;
        d = this.$q.defer();
        this._load().then((function(_this) {
          return function() {
            return d.resolve(_persons[_maps.ids[id]] || null);
          };
        })(this));
        return d.promise;
      };

      return DeskPRO_Service_Person;

    })();
  });

}).call(this);

//# sourceMappingURL=Person.js.map
