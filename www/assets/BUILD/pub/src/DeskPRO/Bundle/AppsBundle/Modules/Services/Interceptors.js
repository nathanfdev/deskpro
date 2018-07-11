const createBoundFunction = (args, fn) => () => fn(...args);

const INVOCATIONTRAP_CMD_RELEASE = () => ({});
const INVOCATIONTRAP_CMD_REARM = () => ({});
const INVOCATIONTRAP_CMD_STATE = () => ({});

const commands = [INVOCATIONTRAP_CMD_REARM, INVOCATIONTRAP_CMD_RELEASE, INVOCATIONTRAP_CMD_STATE];

/**
 * Traps the parameters from the first invocation of handler and when released invokes the handler with those params
 *
 * @param {function} handler
 * @return {function(...[*]=)}
 */
export const createHandlerTrap = (handler) => {
  const initialState = { active: true, boundHandler: null };
  let state = { ...initialState };
  return (...args) => {
    const cmd = args.length === 1 && commands.indexOf(args[0]) > -1 ? args[0] : null;
    const { active, boundHandler } = state;

    if (cmd === INVOCATIONTRAP_CMD_STATE) { return { ...state }; } // should probably clone state

    if (active && cmd === null) {
      state = { active: false, boundHandler: createBoundFunction(args, handler) };
      return null;
    }

    if (active === false && boundHandler !== null && cmd === INVOCATIONTRAP_CMD_RELEASE) {
      state = { ...initialState };
      return boundHandler();
    }

    if (active === false && cmd === INVOCATIONTRAP_CMD_REARM) {
      state = { ...initialState };
      return null;
    }

    return null;
  };
};

/**
 * Creates a function which returns the active status of the trap
 *
 * @param trap
 */
export const createIsTrapActive = trap => () => {
  const state = trap(INVOCATIONTRAP_CMD_STATE);
  return state.active;
};

/**
 * Creates a function which when invoked puts the trap in listening state again
 *
 * @param trap
 */
export const createRearmTrap = trap => () => trap(INVOCATIONTRAP_CMD_REARM);

/**
 * Creates a function which when invoked releases the trap
 *
 * @param trap
 */
export const createReleaseTrap = trap => () => trap(INVOCATIONTRAP_CMD_RELEASE);

/**
 * Creates a function wrapper that invokes an interceptor function before invoking the wrapped function.
 *
 * The interceptor can prevent the invocation of the wrapped function by returning an object like this: { status:true, result: * } where result being the returned value
 *
 * @param {function(=*):{ status:Boolean, result:* }} interceptor
 * @param {function} next the intercepted function
 * @return {function}
 */
export function createInterceptor(interceptor, next) {
  return function intercept(...args)  {
    const { status, result } = interceptor(...args);
    if (status) {
      return result;
    }

    return next(...args);
  };
}
