define [
  'DeskPRO/Util/Util'
], (
  Util
) ->
  class EmailBanEditFormMapper

    ###
      #
    #
    ###

    getFormFromModel: (model) ->

      form = {}

      form.banned_email = model.email_ban.banned_email

      return form

    ###
      #
      #
    ###

    applyFormToModel: (model, formModel) ->

      # we use data from backend, so no need in applying of form data to list model

    ###
      #
      #
    ###

    getPostDataFromForm: (formModel) ->

      postData = {}

      postData.banned_email = formModel.banned_email

      return postData