define ->
  ###*
  * A DataService class handles fetching data from the datastore (API),
    * keeping it, and updating it.
  ###
  class Admin_Main_DataService_Base
    constructor: (em) ->
      @_is_ds_class = true
      @reg_ctrl = []
      @em = em

    ###*
    * Multiple controllers can "register" their interest in a data service.
    * When every controller is dead (e.g., changed view), then the data service
    * is cleaned up (cached object collections are removed).
      *
      * @param {Admin_Ctrl_Base} ctrl
    ###
    registerCtrl: (ctrl) ->
      @reg_ctrl.push(ctrl) unless @reg_ctrl.indexOf(ctrl) != -1
      return @reg_ctrl


    ###*
    * Unregister a controllers interest in this service. This is so
      * we can clean up any cached objects when the view changes.
      *
      * @param {Admin_Ctrl_Base} ctrl
    ###
    unregisterCtrl: (ctrl) ->
      pos = @reg_ctrl.indexOf(ctrl)
      if pos == -1 then return false

      @reg_ctrl.splice(pos, 1)
      return true


    ###*
    * Count how many controllers are currently registered
      *
      * @return {Integer}
    ###
    countCtrl: ->
      return @reg_ctrl.length


    ###*
      * Cleans up the data service state. This will throw an exception
      * when there are still registered controllers.
    ###
    cleanup: ->
      if @reg_ctrl.length
        throw new Error("Cannot cleanup when there are still registered controllers")

      return @_cleanup()

    ###*
      * Sub-classes should implement this method to do actual cleanup.
      *
      * @return void
    ###
    _cleanup: ->
      return