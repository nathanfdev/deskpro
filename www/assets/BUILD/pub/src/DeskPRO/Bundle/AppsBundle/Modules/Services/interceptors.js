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
