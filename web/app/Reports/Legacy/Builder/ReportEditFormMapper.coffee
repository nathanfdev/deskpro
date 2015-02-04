define [
  'DeskPRO/Util/Util'
], (
  Util
) ->
  class ReportEditFormMapper

    ###
      #
    #
    ###
    getFormFromModel: (model) ->

      form = {}
      form.id = model.report.id
      form.title = model.report.title
      form.description = model.report.description

      return form


    ###
      #
      #
    ###
    applyFormToModel: (model, formModel) ->

      model.title = formModel.title


    ###
      #
      #
    ###
    getPostDataFromForm: (formModel) ->

      postData = {}
      postData.id = formModel.id
      postData.title = formModel.title
      postData.description = formModel.description

      return postData