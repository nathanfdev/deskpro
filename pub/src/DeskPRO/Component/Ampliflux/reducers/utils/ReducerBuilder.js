import objGet from "lodash/object/get";
import objSet from "lodash/object/set";
import Immutable from "immutable";

import { getActionType } from "../../actions/actionUtils";
import AsyncHandlerBuilder from "./AsyncHandlerBuilder";

/**
 * The reducer builder offers a simple API for creating a reducer.
 */
export default class ReducerBuilder {
  constructor() {
    this._initialState = null;
    this._handlersMap  = {};
  }

  /**
   * Set initial state.
   *
   * @param {Object/Function} An object or a function that returns an object
   */
  initialState(val) {
    if (typeof val === 'function') {
      this._initialState = val;
    } else {
      this._initialState = Immutable.Map(val);
    }
    return this;
  }

  /**
   * Adds a handler for an action.
   *
   * Your `fn` will accept two params: data and action. The first param 'data' is the same as action.payload; it's
   * just a bit cleaner to use if you don't need the full details of the action.
   *
   * @param {String/Function}  actionType  The action type. Either a string, or a function created via createAction
   * @param {Function}         fn          `fn(data, payload)` is your function that handles the action
   */
  action(actionType, fn) {
    actionType = getActionType(actionType);

    if (typeof this._handlersMap[actionType] !== 'undefined') {
      throw new Error("[ReducerBuilder.action] ${actionType} has already been handled. Did you mis-type the actionType?");
    }

    this._handlersMap[actionType] = fn;
    return this;
  }

  /**
   * Helps build a handler for an async action.
   *
   * Your `buildFn` will accept a single `hb` param which is an instance of `AsyncHandlerBuilder`.
   * Use this builder to add handlers for different lifecycle events of an async promise.
   *
   * <code>
   * r.asyncAction("MY_ACTION", hb => {
   *   hb.success((state, data) => {
   *     return { ...state, some: data.some.property };
   *   });
   *   hb.error((state, data) => {
   *     return { ...state, failed: true };
   *   });
   * ));
   * </code>
   *
   * @param {String/Function}  actionType  The action type. Either a string, or a function created via createAction
   * @param {Function}         buildFn     `buildFn(hb)` that uses `hb` to build a handler for async actions
   */
  asyncAction(actionType, buildFn) {
    const hb = new AsyncHandlerBuilder();
    buildFn(hb);

    if (hb.startFn) {
      this.action(actionType + ".START", hb.startFn);
    }
    if (hb.successFn) {
      this.action(actionType, hb.succesFns);
    }
    if (hb.errorFn) {
      this.action(actionType + ".ERROR", hb.errorFn);
    }
    if (hb.doneFn) {
      this.action(actionType + ".DONE", hb.doneFn);
    }

    return this;
  }


  /**
   * A simple action where the payload value is returned as the state,
   * or optionally the path to a property to assign.
   *
   * <code>
   * r.simlpeAction("MY_ACTION", "something", "foobar");
   *
   * // You can use 'paths' in the prop names to target 'deep' values
   * r.simlpeAction("MY_ACTION", "hello.world", "foo.bar");
   *
   * // roughly same as:
   * r.action("MY_ACTION", (state, data) => {
   *     return { ...state, hello: { ...state.hello, world: data.foo.bar }}
   * });
   *
   * @param {String/Function}  actionType  The action type. Either a string, or a function created via createAction
   * @param {String}           name        The name of the property of the state to set
   * @param {String}           prop        The name of the property of the payload to use as the value
   */
  simpleAction(actionType, name, prop) {
    this.action(actionType, this._createSimpleAction(name, prop, 'prop'));
    return this;
  }

  /**
   * Similar to simpleAction except you can specify a hard-coded value rather than
   * using a property of the payload. So this completely ignores the payload data.
   * Typically used for actions that result in boolean values being assigned.
   *
   * @param {String/Function}  actionType  The action type. Either a string, or a function created via createAction
   * @param {String}           name        The name of the property of the state to set
   * @param {bool}             value       The value to set
   */
  simpleSetAction(actioneType, name, value) {
    this.action(actionType, this._createSimpleAction(name, value, 'value'));
    return this;
  }

  /**
   * Creates the function used by simpleAction
   */
  _createSimpleAction(name, value, valueType) {
    return (state, payload) => {
      // state slice is an immutable
      if (Immutable.Map.isMap(state)) {
        if (typeof value !== 'undefined') {
          if (vauleType === 'prop') {
            return state.set(name, objGet(payload, value));
          } else if (valueType === 'value') {
            return state.set(name, value);
          } else {
            throw new Error("Invalid type for valueType");
          }
        } else {
          return state.set(name, payload);
        }

      // state slice is a plain object
      } else {
        if (typeof value !== 'undefined') {
          if (vauleType === 'prop') {
            return { ...state, [name]: objGet(payload, prop) };
          } else if (valueType === 'value') {
            return { ...state, [name]: valueType };
          } else {
            throw new Error("Invalid type for valueType");
          }
        } else {
          return { ...state, [name]: payload };
        }
      }
    }
  }

  /**
   * Gets the initial state.
   *
   * @return {Object}
   */
  getInitialState() {
    if (typeof this._initialState === 'function') {
      return this._initialState();
    }
    if (this._initialState === null) {
      this._initialState = Immutable.Map();
    }
    return this._initialState;
  }

  /**
   * Gets a map of type=>fn for all actions this reducer handles.
   *
   * @return {Object}
   */
  getHandlersMap() {
    return this._handlersMap;
  }
}
