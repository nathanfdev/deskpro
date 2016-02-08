/**
 * Base DAL repository class
 */
export class AbstractRepository {
  constructor() {
    const methods = [
      'load',
      'loadBatch',
      'update',
      'remove',
      'removeBatch'
    ];

    for (const method of methods) {
      if (this[method] === undefined) {
        throw new TypeError(`AbstractRepository must define ${method} method`);
      }
    }
  }
}
