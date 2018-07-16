
export default function setTimeoutImplementation() {
  const isBrowser = new Function('try {return this===window;}catch(e){ return false;}'); // eslint-disable-line no-new-func

  const isNode = new Function('try {return this===global;}catch(e){return false;}'); // eslint-disable-line no-new-func

  if (isBrowser()) {
    return window.setTimeout;
  }

  if (isNode()) {
    return setTimeout;
  }

  return function (handler, timeout) { // eslint-disable-line no-unused-vars
    handler();
  };
}
