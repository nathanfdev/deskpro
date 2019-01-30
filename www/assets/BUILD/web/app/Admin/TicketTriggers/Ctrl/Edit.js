define [
  'DeskPRO/Util/Arrays'
  'Admin/Main/Ctrl/Base',
  'Admin/TicketTriggers/TriggerEditFormMapper',
  'Admin/TicketTriggers/Ctrl/EditBase',
], (
  Arrays,
  Admin_Ctrl_Base,
  TriggerEditFormMapper,
  Admin_TicketTriggers_Ctrl_EditBase
) ->
  class Admin_TicketTriggers_Ctrl_Edit extends Admin_TicketTriggers_Ctrl_EditBase
    @CTRL_ID   = 'Admin_TicketTriggers_Ctrl_Edit'
    @CTRL_AS   = 'TicketTriggersEdit'
    @DEPS      = ['dpObTypesDefTicketCriteria', 'dpObTypesDefTicketActions']

  Admin_TicketTriggers_Ctrl_Edit.EXPORT_CTRL()