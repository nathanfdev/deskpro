// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(function() {
  /**
  * A DataService class handles fetching data from the datastore (API),
    * keeping it, and updating it.
  */
  let Admin_Main_DataService_Base;
  return (Admin_Main_DataService_Base = class Admin_Main_DataService_Base {
    constructor(em) {
      this._is_ds_class = true;
      this.reg_ctrl = [];
      this.em = em;
    }

    /**
    * Multiple controllers can "register" their interest in a data service.
    * When every controller is dead (e.g., changed view), then the data service
    * is cleaned up (cached object collections are removed).
      *
      * @param {Admin_Ctrl_Base} ctrl
    */
    registerCtrl(ctrl) {
      if (this.reg_ctrl.indexOf(ctrl) === -1) { this.reg_ctrl.push(ctrl); }
      return this.reg_ctrl;
    }


    /**
    * Unregister a controllers interest in this service. This is so
      * we can clean up any cached objects when the view changes.
      *
      * @param {Admin_Ctrl_Base} ctrl
    */
    unregisterCtrl(ctrl) {
      const pos = this.reg_ctrl.indexOf(ctrl);
      if (pos === -1) { return false; }

      this.reg_ctrl.splice(pos, 1);
      return true;
    }


    /**
    * Count how many controllers are currently registered
      *
      * @return {Integer}
    */
    countCtrl() {
      return this.reg_ctrl.length;
    }


    /**
      * Cleans up the data service state. This will throw an exception
      * when there are still registered controllers.
    */
    cleanup() {
      if (this.reg_ctrl.length) {
        throw new Error("Cannot cleanup when there are still registered controllers");
      }

      return this._cleanup();
    }

    /**
      * Sub-classes should implement this method to do actual cleanup.
      *
      * @return void
    */
    _cleanup() {
    }
  });
});