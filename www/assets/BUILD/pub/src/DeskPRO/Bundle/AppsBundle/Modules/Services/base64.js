export class Base64Converter {
  constructor(window) {
    this.window = window;
  }

  /**
   * @param {string} payload
   * @return {string}
   */
  encode(payload) {
    return this.window.btoa(payload);
  }

  /**
   * @param {string} payload
   * @return {string}
   */
  decode(payload) {
    return this.window.atob(payload);
  }

  /**
   * @param {string|object} payload
   * @return {string}
   */
  encodeJSON(payload) {
    return this.encode(JSON.stringify(payload));
  }

  /**
   * @param {string} payload
   * @return {*}
   */
  decodeJSON(payload) {
    return JSON.parse(this.decode(payload));
  }
}

export class Base64ConverterNodeJs {
  /**
   *
   * @param {string} payload
   * @return {string}
   */
  encode(payload) { // eslint-disable-line class-methods-use-this
    return Buffer.from(payload).toString('base64');
  }

  decode(payload) { // eslint-disable-line class-methods-use-this
    return Buffer.from(payload).toString('utf8');
  }

  /**
   * @param {string|object} payload
   * @return {string}
   */
  encodeJSON(payload) {
    return this.encode(JSON.stringify(payload));
  }

  /**
   * @param {string} payload
   * @return {*}
   */
  decodeJSON(payload) {
    return JSON.parse(this.decode(payload));
  }
}
