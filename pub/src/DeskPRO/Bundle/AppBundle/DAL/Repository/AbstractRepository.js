/**
 * Base DAL repository class
 */
export class AbstractRepository {
  constructor() {
    if (this.constructor.name === 'AbstractRepository') {
      throw new TypeError("Can't instantiate AbstractRepository");
    }

    const methods = [
      'load',
      'loadBatch',
      'update',
      'remove',
      'removeBatch'
    ];

    for (const method of methods) {
      if (this[method] === undefined) {
        throw new TypeError(`${this.constructor.name} must define ${method} method`);
      }
    }
  }
}
