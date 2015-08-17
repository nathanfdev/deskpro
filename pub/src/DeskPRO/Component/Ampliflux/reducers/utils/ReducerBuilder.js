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
    this._initialState = {};
    this._handlersMap  = {};
  }

  /**
   * @param {Object/Function} An object or a function that returns an object
   */
  initialState(val) {
    this._initialState = val;
    return this;
  }

  /**
   * Adds a handler for an action.
   *
   * Your `fn` will accept three params: state, data and action. The first param 'data' is the same as action.payload; it's
   * just a bit cleaner to use if you don't need the full details of the action.
   *
   * @param {String/Function}  actionType  The action type. Either a string, or a function created via createAction
   * @param {Function}         fn          `fn(data, payload)` is your function that handles the action
   */
  handleSet(actionType, fn) {
    actionType = getActionType(actionType);

    if (typeof this._handlersMap[actionType] !== 'undefined') {
      throw new Error("[ReducerBuilder.action] ${actionType} has already been handled. Did you mis-type the actionType?");
    }

    this._handlersMap[actionType] = fn;
    return this;
  }

  /**
   * Adds a handler that will call `fn(data, action, currentState)` which is expected
   * to return a Map/object which will be merged into the current state.
   *
   * @param {String/Function}  actionType  The action type. Either a string, or a function created via createAction
   * @param {Function}         fn
   */
  handle(actionType, fn, deep = false) {
    this.handleSet(actionType, (state, data, action) => {
      const partial  = Immutable.Map(fn(data, action, state));
      const oldState = Immutable.Map.isMap(state) ? state : Immutable.Map(state);
      return deep ? oldState.mergeDeep(partial) : oldState.merge(partial);
    });
    return this;
  }

  /**
   * Shortcut for a handler that will set value on the store using
   * data from the payload.
   *
   * @param {String/Function}  actionType      The action type. Either a string, or a function created via createAction
   * @param {String}           statePropKey    The key of the value in the store
   * @param {String}           payloadPropKey  The key of the value in data payload to set
   */
  handleProperty(actionType, statePropKey, payloadPropKey) {
    this.handleSet(actionType, (state, data, action) => {
      const payloadVal = Immutable.Map.isMap(data) ? data.getIn(payloadPropKey.split('.')) : objGet(data, payloadPropKey);
      const oldState   = Immutable.Map.isMap(state) ? state : Immutable.Map(state);
      return oldState.mergeIn(statePropKey.split('.'), payloadVal);
    });
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
  handleAsync(actionType, buildFn) {
    const hb = new AsyncHandlerBuilder();
    buildFn(hb);

    if (hb.startFn) {
      this.handle(actionType + ".START", hb.startFn);
    }
    if (hb.successFn) {
      this.handle(actionType, hb.succesFns);
    }
    if (hb.errorFn) {
      this.handle(actionType + ".ERROR", hb.errorFn);
    }
    if (hb.doneFn) {
      this.handle(actionType + ".DONE", hb.doneFn);
    }

    return this;
  }

  /**
   * Shortcut for creating a handler that sets `isLoaded` flag on state for an async action.
   *
   * @param {String/Function}  actionType  The action type. Either a string, or a function created via createAction
   */
  handleAsyncWithStatus(actionType, propName = 'isLoaded') {
    this.handleAsync(actionType, b => b.handleLoading(propName));
    return this;
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
