/**
 * Base DAL repository class
 */
export class AbstractRepository {
  constructor() {
    if (new.target === AbstractRepository) {
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
        throw new TypeError(`${new.target} must define ${method} method`);
      }
    }
  }
}
