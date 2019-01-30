// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'DeskPRO/Util/Arrays',
  'Admin/Main/Ctrl/Base',
  'Admin/TicketTriggers/TriggerEditFormMapper',
  'Admin/TicketTriggers/Ctrl/EditBase',
], function(
  Arrays,
  Admin_Ctrl_Base,
  TriggerEditFormMapper,
  Admin_TicketTriggers_Ctrl_EditBase
) {
  class Admin_TicketTriggers_Ctrl_Edit extends Admin_TicketTriggers_Ctrl_EditBase {
    static initClass() {
      this.CTRL_ID   = 'Admin_TicketTriggers_Ctrl_Edit';
      this.CTRL_AS   = 'TicketTriggersEdit';
      this.DEPS      = ['dpObTypesDefTicketCriteria', 'dpObTypesDefTicketActions'];
    }
  }
  Admin_TicketTriggers_Ctrl_Edit.initClass();

  return Admin_TicketTriggers_Ctrl_Edit.EXPORT_CTRL();
});