define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_ExportCsv_Ctrl_ExportCsv extends Admin_Ctrl_Base

    @CTRL_ID   = 'Admin_ExportCsv_Ctrl_ExportCsv'
    @CTRL_AS   = 'Ctrl'
    @DEPS      = ['Api', 'Growl', '$http']

    init: ->

    initialLoad: ->




  Admin_ExportCsv_Ctrl_ExportCsv.EXPORT_CTRL()