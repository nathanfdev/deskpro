export class Base64Converter {
  constructor(window) {
    this.window = window;
  }

  encode = payload => window.btoa(payload);

  decode = payload => window.atob(payload);

  /**
   * @param {string|object} payload
   * @return {string}
   */
  encodeJSON = payload => this.encode(JSON.stringify(payload));

  /**
   * @param {string} payload
   * @return {*}
   */
  decodeJSON = payload => JSON.parse(this.decode(payload));
}
