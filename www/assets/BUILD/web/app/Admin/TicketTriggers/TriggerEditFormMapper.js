define [
  'underscore'
], (
  _
) ->
  class Admin_TicketTriggers_TriggerEditFormMapper
    getFormFromModel: (model, forceModeMapping = false) ->
      form = {}
      form.title = model.title || ''

      if model.id or forceModeMapping == true
        form.typeForm = {
          by_user: false,
          by_agent: false,
          by_app: false,
          by_agent_mode: {
            web: false,
            email: false,
            api: false
          },
          by_user_mode: {
            portal: false,
            widget: false,
            form: false,
            email: false,
            api: false
          },
          by_app_mode: {}
        }

        if model.by_agent_mode.length
          form.typeForm.by_agent = true
          for x in model.by_agent_mode
            form.typeForm.by_agent_mode[x] = true
        if model.by_user_mode.length
          form.typeForm.by_user = true
          for x in model.by_user_mode
            form.typeForm.by_user_mode[x] = true
        if model.by_app_mode.length
          form.typeForm.by_app = true
          for x in model.by_app_mode
            form.typeForm.by_app_mode[x] = true
      else
        form.typeForm = {
          by_user: true,
          by_agent: true,
          by_app: false,
          by_agent_mode: {
            web: true,
            email: true,
            api: true
          },
          by_user_mode: {
            portal: true,
            widget: true,
            form: true,
            email: true,
            api: true
          },
          by_app_mode: {}
        }

      form.flags = {}
      if model?.event_flags?.indexOf('run_newreply') != -1
        form.flags.run_newreply = true
      else
        form.flags.run_newreply = false

      form.terms_set = {}
      form.actions = {}

      if model.terms?.terms?.length
        for termSet in model.terms.terms
          if not termSet.set_terms or not termSet.set_terms.length then continue
          setId = _.uniqueId('termset')
          form.terms_set[setId] = {}

          for term in termSet.set_terms
            rowId = _.uniqueId('term')
            form.terms_set[setId][rowId] = term

      if model.actions?.actions?.length
        for action in model.actions.actions
          rowId = _.uniqueId('action')
          form.actions[rowId] = action

      return form