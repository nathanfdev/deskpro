export class Base64Converter {
  constructor(window) {
    this.window = window;
  }

  /**
   * @param {string|object} payload
   * @return {string}
   */
  encode = (payload) => {
    if (typeof payload === 'string') {
      return window.btoa(payload);
    }

    if (typeof payload === 'object') {
      return window.btoa(JSON.stringify(payload));
    }

    throw new Error('Base64Converter.encode method accepts only strings or plain objects');
  };

  /**
   * @param {string} string
   * @return {string}
   */
  decode = string => window.atob(string);
}
