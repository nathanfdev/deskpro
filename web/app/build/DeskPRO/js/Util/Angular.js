(function() {
  var __hasProp = {}.hasOwnProperty;

  define(function() {
    var DeskPRO_Util_Angular;
    DeskPRO_Util_Angular = (function() {
      function DeskPRO_Util_Angular() {}


      /*
        	 * Get an object of k=>v services injected into a constructor of object given an array of args.
        	 *
        	 * @param {Object} object An object annotated with $inject
        	 * @param {Array} args    An array of args, typically args of a constructor
        	 * @return {Object}
       */

      DeskPRO_Util_Angular.prototype.getInjectedArgs = function(object, args) {
        var arg, argName, i, injectedArgs, injectedNames, _i, _len;
        injectedArgs = {};
        injectedNames = null;
        if (object.$inject) {
          injectedNames = object.$inject;
        } else if (object.constructor.$inject) {
          injectedNames = object.constructor.$inject;
        }
        if (!injectedNames) {
          injectedArgs;
        }
        for (i = _i = 0, _len = args.length; _i < _len; i = ++_i) {
          arg = args[i];
          argName = injectedNames[i];
          injectedArgs[argName] = arg;
        }
        return injectedArgs;
      };


      /*
        	 * Takes the objects injected (gotten via getInjectedArgs) and assigns them to properties
        	 * on the object,
        	 *
        	 * @param {Object} object An object annotated with $inject
        	 * @param {Array} args    An array of args, typically args of a constructor
       */

      DeskPRO_Util_Angular.prototype.setInjectedProperties = function(object, args) {
        var injectedArgs, k, v, _results;
        injectedArgs = this.getInjectedArgs(object, args);
        _results = [];
        for (k in injectedArgs) {
          if (!__hasProp.call(injectedArgs, k)) continue;
          v = injectedArgs[k];
          _results.push(object[k] = v);
        }
        return _results;
      };

      return DeskPRO_Util_Angular;

    })();
    return new DeskPRO_Util_Angular();
  });

}).call(this);

//# sourceMappingURL=Angular.js.map
