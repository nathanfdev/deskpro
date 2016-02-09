import { AbstractRepository } from './AbstractRepository';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';

/**
 * DeskPRO API record repository
 */
export class ApiRepository extends AbstractRepository {

  /**
   * @param api
   * @param url
   * @param supportsLoadAll
   */
  constructor(api, url, supportsLoadAll = false) {
    super();
    this.api = api;
    this.url = url.replace(/^\/+/, '');
    this.supportsLoadAll = supportsLoadAll;
  }

  /**
   * @param id
   * @returns {*}
   */
  load(id) {
    return this.api.sendGet(`DP_API/${this.url}/${id}`);
  }

  /**
   * @param ids
   * @returns {*}
   */
  loadBatch(ids) {
    return this.api.sendGet(`DP_API/${this.url}?ids=` + ids.join(','));
  }

  /**
   * @returns {*}
   */
  loadAll() {
    if (!this.supportsLoadAll) {
      throw new Error(`${this.url} endpoint is not allowed to loadAll()`);
    }

    return this.api.sendGet(`DP_API/${this.url}`);
  }

  /**
   * @param params
   * @param include
   * @returns {*}
   */
  search(params, include) {
    return this.api.sendGet(`DP_API/${this.url}?` + this.compileParams(include ? {...params, include} : params));
  }

  /**
   * @param record
   * @param id
   * @returns {*}
   */
  update(record, id = null) {
    if (!id && !record.hasOwnProperty('id')) {
      throw Error("Can't resolve record ID");
    }

    const recordId = id ? id : record['id'];

    return this.api.sendPut(`DP_API/${this.url}/${recordId}`, record);
  }

  /**
   * @param target
   * @returns {*}
   */
  remove(target) {
    return this.api.sendDelete(`DP_API/${this.url}/${this.getId(target)}`);
  }

  /**
   * @param targets
   * @returns {*}
   */
  removeBatch(targets) {
    const ids = [];
    for (const target of targets) {
      ids.push(this.getId(target));
    }

    return this.api.sendDelete(`DP_API/${this.url}?ids=${ids.join(',')}`);
  }

  // Protected methods -------------------------------------------------------------------------------------------------

  /**
   * @param target
   * @returns {*}
   */
  getId(target) {
    let id;
    if (typeof target === 'object') {
      if (!target.hasOwnProperty('id')) {
        throw new Error('Target must be either a numeric ID or an object with numeric "id" property');
      }
      id = target.id;
    } else {
      id = target;
    }

    return id;
  }

  /**
   * @param params
   */
  compileParams(params) {
    return compileParams(params);
  }
}