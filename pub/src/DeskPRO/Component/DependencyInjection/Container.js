import _ from "lodash";

export class InvalidNameException {}
export class CyclicDependencyException {}
export class InvalidRegisterType {}
export class InvalidProviderType {}

export default class Container {
  constructor() {
    this.values = {
      "injector": this
    };
    this.providers = {};
    this.aliases = {};

    // Map of objects being built right now
    // used for detecting cyclic dependencies
    this.isBuilding = {};
  }

  /**
   * Register a new service, factory or constant.
   *
   * Config:
   * - args:     Array of depdendencies. Services should be named @service.name, constant values are just regular JS values.
   * - factory:  A factory function. The return value is the value assigned to the service name
   * - provider: A function that sholud return an object with a 'get' method, which will be called like a factory function. The function can be annotated with it's own deps.
   * - service:  A constructor function. It will be called with 'new'.
   * - constant: A constant value.
   * - alias or aliases: A name or array of names that the service is also known by
   *
   * @param {String} name
   * @param {Object} config
   */
  register(name, config) {
    let args = [];
    let locals = config.locals || {};
    let hasLocals = !!config.locals || false;

    if (config.args) {
      for (let n = 0; n < config.args.lenght; i++) {
        let val = config.args[n];

        // A named reference
        if (_.isString(val) && val[0] == "@") {
          args.push(val.substr(1));

          // A value
        } else {
          args.push("dp_local_" + n);
          locals["dp_local_" + n] = val;
          hasLocals = false;
        }
      }
    }

    // Factory
    if (config.factory) {
      args.push(config.factory);
      this.setFactory(name, () => {
        return this.invoke(args, null, locals);
      });

      // Provider
    } else if (config.provider) {
      args.push(config.provider);
      this.setFactory(name+'Provider', () => {
        return this.invoke(args, null, locals);
      });

      this.setFactory(name, [name+'Provider', (provider) => {
        if (!provider.get) {
          throw new InvalidProviderType();
        }

        return this.invoke(provider.get);
      }]);

      // Service
    } else if (config.service) {
      args.push(config.service);
      this.setFactory(name, () => {
        return this.invokeConstructor(args, locals);
      });

      // Constant/value
    } else if (config.constant) {
      this.registerValue(name, config.constant);
    } else if (config.value) {
      this.registerValue(name, config.value);

    // Error
    } else {
      throw new InvalidRegisterType();
    }

    if (config.aliases) {
      (_.isArray(config.aliases) ? config.aliases : [config.aliases]).map(n => this.aliases[n] = name);
    }
    if (config.alias) {
      (_.isArray(config.alias) ? config.alias : [config.alias]).map(n => this.aliases[n] = name);
    }
  }


  /**
   * Set a value for name.
   *
   * @param {String} name
   * @param {*} value
   */
  registerValue(name, value) {
    this.values[name] = value;
  }

  /**
   * Set a factory method for name.
   *
   * @param {String} name
   * @param {Function/Array} fn
   */
  registerFactory(name, fn) {
    this.providers[name] = fn;
  }

  /**
   * Invoke the injector on a function.
   *
   * @param {Function/Array} fn
   * @param {Object} self
   * @param {Object} locals
   */
  invoke(fn, self = null, locals = null) {
    let argNames;

    // [a, b, function() {}] syntax
    if (_.isArray(fn)) {
      let tmp = fn;
      fn = tmp.pop();
      argNames = tmp;

    // fn.$inject syntax
    } else if (fn.$inject) {
      argNames = fn.$inject;
    } else {
      argNames = [];
    }

    let args = this.getNames(argNames, locals);

    return fn.apply(self, args);
  }

  /**
   * Invoke the injector on a constructor.
   *
   * @param {Function} fn
   * @param {Object}   locals
   */
  invokeConstructor(fn, locals = null) {
    let argNames;

    // [a, b, function() {}] syntax
    if (_.isArray(fn)) {
      let tmp = fn;
      fn = tmp.pop();
      argNames = tmp;

      // fn.$inject syntax
    } else if (fn.$inject) {
      argNames = fn.$inject;
    } else {
      argNames = [];
    }

    let args = this.getNames(argNames, locals);

    switch (args.length) {
      case 0:  return new fn();
      case 1:  return new fn(args[0]);
      case 2:  return new fn(args[0], args[1]);
      case 3:  return new fn(args[0], args[1], args[2]);
      case 4:  return new fn(args[0], args[1], args[2], args[3]);
      case 5:  return new fn(args[0], args[1], args[2], args[3], args[4]);
      case 6:  return new fn(args[0], args[1], args[2], args[3], args[4], args[5]);
      case 7:  return new fn(args[0], args[1], args[2], args[3], args[4], args[5], args[6]);
      case 8:  return new fn(args[0], args[1], args[2], args[3], args[4], args[5], args[6], args[7]);
      case 9:  return new fn(args[0], args[1], args[2], args[3], args[4], args[5], args[6], args[7], args[8]);
      case 10: return new fn(args[0], args[1], args[2], args[3], args[4], args[5], args[6], args[7], args[8], args[9]);
      case 11: return new fn(args[0], args[1], args[2], args[3], args[4], args[5], args[6], args[7], args[8], args[9], args[10]);
      case 12: return new fn(args[0], args[1], args[2], args[3], args[4], args[5], args[6], args[7], args[8], args[9], args[10], args[11]);
      case 13: return new fn(args[0], args[1], args[2], args[3], args[4], args[5], args[6], args[7], args[8], args[9], args[10], args[11], args[12]);
      case 14: return new fn(args[0], args[1], args[2], args[3], args[4], args[5], args[6], args[7], args[8], args[9], args[10], args[11], args[12], args[13]);
      case 15: return new fn(args[0], args[1], args[2], args[3], args[4], args[5], args[6], args[7], args[8], args[9], args[10], args[11], args[12], args[13], args[14]);
      default: throw "no generated constructor call";
    }
  }

  /**
   * Resolves an array of names or a map of names.
   *
   * @param {Array/Object} names
   * @param {Object}       locals
   * @returns {Array/Object}
   */
  getNames(names, locals = null) {
    if (_.isArray(names)) {
      return names.map(n => locals && locals[n] ? locals[n] : this.get(n));
    } else {
      let map = {};
      for (let n of names) {
        map[n] = locals && locals[n] ? locals[n] : this.get(n);
      }

      return map;
    }
  }

  /**
   * Resolve a registered name
   *
   * @param {String} name
   * @returns {Object}
   */
  get(name) {
    if (this.aliases[name]) {
      name = this.aliases[name];
    }

    if (this.values[name]) {
      return this.values[name];
    } else if (this.providers[name]) {
      if (this.isBuilding[name]) {
        throw new CyclicDependencyException();
      }
      this.isBuilding[name] = true;
      let i = this.invoke(this.providers[name]);
      delete this.isBuilding[name];

      this.values[name] = i;
      return i;
    }

    throw new InvalidNameException();
  }

  /**
   * Check if a service exists.
   *
   * @param {String} name
   * @returns {boolean}
   */
  has(name) {
    return !!this.values[name] || !!this.providers[name];
  }
}