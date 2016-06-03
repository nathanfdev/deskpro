import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';

/**
 * DeskPRO API record repository
 */
export class ApiRepository {

  /**
   * @param {DpApi} api DeskPRO api service
   * @param {string} url basic url to interact
   * @param {bool} supportsLoadAll index if repository supports loading all models
   *
   * @returns {void}
   */
  constructor(api, url, supportsLoadAll = false) {
    this.api = api;
    this.url = url.replace(/^\/+/, '');
    this.supportsLoadAll = supportsLoadAll;
  }

  /**
   * @param {integer} id Identity to load model
   * @returns {Promise} promise
   */
  load(id) {
    return this.api.sendGet(`DP_API/${this.url}/${id}`);
  }

  /**
   * @param {integer} ids Identities to load model
   * @returns {Promise} promise
   */
  loadBatch(ids) {
    return this.api.sendGet(`DP_API/${this.url}?ids=` + ids.join(','));
  }

  /**
   * @throws {Error}
   * @returns {Promise} promise
   */
  loadAll() {
    if (!this.supportsLoadAll) {
      throw new Error(`${this.url} endpoint is not allowed to loadAll()`);
    }

    return this.api.sendGet(`DP_API/${this.url}`);
  }

  /**
   * @param {object} params Additional parameters to request
   * @param {string} include Include string for sideloading
   * @returns {Promise} promise
   */
  search(params, include) {
    console.log('Params', params);
    return this.api.sendGet(`DP_API/${this.url}?` + this.compileParams(include ? {...params, include} : params));
  }

  /**
   * @param {object} record Model of record to create at server side
   * @returns {Promise} promise
   */
  create(record) {
    return this.api.sendPost(`DP_API/${this.url}`, record);
  }

  /**
   * @throws {Error} if record.id or id undefined
   * @param {object|array} record Model of record to update
   * @param {integer} id Model identity
   * @returns {Promise} Promise
   */
  update(record, id = null) {
    if (!id && !record.hasOwnProperty('id')) {
      throw Error("Can't resolve record ID");
    }
    const recordId = id ? id : record.id;

    return this.api.sendPut(`DP_API/${this.url}/${recordId}`, record);
  }

  /**
   * @param {object|integer} target Model or its identity to delete
   * @returns {Promise} Promise
   */
  remove(target) {
    return this.api.sendDelete(`DP_API/${this.url}/${this.getId(target)}`);
  }

  /**
   * @param {integer[]|object[]} targets An array of models or their identities to delete
   * @returns {Promise} Promise
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
   * @param {object|integer} target Model or identity
   * @throws {Error} If target is not scalar or target.id is undefined
   * @returns {Promise} Promise
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
   * @param {object} params Parameters to compile params in query string
   * @returns {string} Ready to query parameters string
   */
  compileParams(params) {
    return compileParams(params);
  }
}
