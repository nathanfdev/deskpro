(function() {
  define(function() {

    /**
    	* A DataService class handles fetching data from the datastore (API),
       * keeping it, and updating it.
     */
    var Admin_Main_DataService_Base;
    return Admin_Main_DataService_Base = (function() {
      function Admin_Main_DataService_Base(em) {
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

      Admin_Main_DataService_Base.prototype.registerCtrl = function(ctrl) {
        if (this.reg_ctrl.indexOf(ctrl) === -1) {
          this.reg_ctrl.push(ctrl);
        }
        return this.reg_ctrl;
      };


      /**
      		* Unregister a controllers interest in this service. This is so
        	* we can clean up any cached objects when the view changes.
        	*
        	* @param {Admin_Ctrl_Base} ctrl
       */

      Admin_Main_DataService_Base.prototype.unregisterCtrl = function(ctrl) {
        var pos;
        pos = this.reg_ctrl.indexOf(ctrl);
        if (pos === -1) {
          return false;
        }
        this.reg_ctrl.splice(pos, 1);
        return true;
      };


      /**
      		* Count how many controllers are currently registered
        	*
        	* @return {Integer}
       */

      Admin_Main_DataService_Base.prototype.countCtrl = function() {
        return this.reg_ctrl.length;
      };


      /**
        	* Cleans up the data service state. This will throw an exception
        	* when there are still registered controllers.
       */

      Admin_Main_DataService_Base.prototype.cleanup = function() {
        if (this.reg_ctrl.length) {
          throw new Error("Cannot cleanup when there are still registered controllers");
        }
        return this._cleanup();
      };


      /**
        	* Sub-classes should implement this method to do actual cleanup.
        	*
        	* @return void
       */

      Admin_Main_DataService_Base.prototype._cleanup = function() {};

      return Admin_Main_DataService_Base;

    })();
  });

}).call(this);

//# sourceMappingURL=Base.js.map
