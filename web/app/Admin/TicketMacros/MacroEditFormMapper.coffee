define ->
  class Admin_TicketMacros_MacroEditFormMapper
    getFormFromModel: (macroModel) ->
      form = {}
      form.title = macroModel.title || ''
      form.is_global = macroModel.is_global
      form.person_id = "0"
      form.actions   = macroModel.actions?.actions || {}

      if macroModel.person
        form.person_id = macroModel.person.id + ""

      return form

    applyFormToModel: (macroModel, formModel) ->
      macroModel.title = formModel.title

    getPostDataFromForm: (formModel) ->
      postData = {}
      postData.title = formModel.title

      if formModel.is_global
        postData.is_global = true
        postData.person_id = null
      else
        postData.is_global = false
        postData.person_id = formModel.person_id

      postData.actions = []
      for own id, row of formModel.actions
        postData.actions.push(row)

      return postData