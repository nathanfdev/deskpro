import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

/**
 * PersonSettingRepository
 */
export class PersonSettingRepository extends ApiRepository {
  /**
   * PersonSetting update is a special case and works w/o ID
   *
   * @param record
   * @param id
   * @returns {*}
   */
  update(record, id = null) {
    return this.api.sendPut(`DP_API/${this.url}`, record);
  }
}
