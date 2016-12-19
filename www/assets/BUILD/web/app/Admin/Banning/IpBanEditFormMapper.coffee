define [
  'DeskPRO/Util/Util'
], (
  Util
) ->
  class IpBanEditFormMapper

    ###
      #
    #
    ###

    getFormFromModel: (model) ->

      form = {}

      form.banned_ip = model.ip_ban.banned_ip

      return form

    ###
      #
      #
    ###

    applyFormToModel: (model, formModel) ->
      formModel = model

    ###
      #
      #
    ###

    getPostDataFromForm: (formModel) ->

      postData = {}

      postData.banned_ip = formModel.banned_ip

      return postData