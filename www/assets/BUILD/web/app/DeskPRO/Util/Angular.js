define(() => {
  class DeskPRO_Util_Angular {
    /*
      * Get an object of k=>v services injected into a constructor of object given an array of args.
      *
      * @param {Object} object An object annotated with $inject
      * @param {Array} args    An array of args, typically args of a constructor
      * @return {Object}
    */
    getInjectedArgs(object, args) {
      const injectedArgs = {};

      let injectedNames = null;
      if (object.$inject) {
        injectedNames = object.$inject;
      } else if (object.constructor.$inject) {
        injectedNames = object.constructor.$inject;
      }

      if (!injectedNames) { injectedArgs; }

      for (let i = 0; i < args.length; i++) {
        const arg = args[i];
        const argName = injectedNames[i];
        injectedArgs[argName] = arg;
      }

      return injectedArgs;
    }


    /*
      * Takes the objects injected (gotten via getInjectedArgs) and assigns them to properties
      * on the object,
      *
      * @param {Object} object An object annotated with $inject
      * @param {Array} args    An array of args, typically args of a constructor
    */
    setInjectedProperties(object, args) {
      const injectedArgs = this.getInjectedArgs(object, args);
      return (() => {
        const result = [];
        for (const k of Object.keys(injectedArgs || {})) {
          const v = injectedArgs[k];
          result.push(object[k] = v);
        }
        return result;
      })();
    }
  }

  return new DeskPRO_Util_Angular();
});
