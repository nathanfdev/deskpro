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

      function DeskPRO_Service_Person($http) {
        this.$http = $http;
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
            return console.info(data);
          };
        })(this)).error((function(_this) {
          return function(data, status, headers, config) {
            return console.error(data, status);
          };
        })(this));
      }

      return DeskPRO_Service_Person;

    })();
  });

}).call(this);

//# sourceMappingURL=Person.js.map
