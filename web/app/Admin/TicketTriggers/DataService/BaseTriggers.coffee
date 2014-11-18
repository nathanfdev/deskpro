define [
  'Admin/Main/DataService/BaseListEdit'
], (
  BaseListEdit,
)  ->
  class Admin_TicketTriggers_DataService_BaseTriggers extends BaseListEdit
    @$inject = ['Api', '$q']

    _doLoadList: ->
      deferred = @$q.defer()

      @Api.sendGet('/ticket_triggers/' + @type).success( (data) =>
        models = data.triggers
        @department_triggers_enabled   = data.department_triggers_enabled || false
        @emailaccount_triggers_enabled = data.emailaccount_triggers_enabled || false
        deferred.resolve(models)
      , (data, status, headers, config) ->
        deferred.reject()
      )

      return deferred.promise

    ###
      # Get all data needed for the edit filter page
      #
      # @param {Integer} id Filter id
      # @return {promise}
    ###
    loadEditTriggerData: (id) ->

      deferred = @$q.defer()

      @Api.sendGet('/ticket_triggers/' + id).then( (result) ->
        deferred.resolve({
          trigger: result.data.trigger
        })
      , ->
        deferred.reject()
      )

      return deferred.promise


    ###
      # Save the enabled state of a trigger
      #
      # @param {Integer} triggerId
      # @param {bool} isEnabled
      # @return {promise}
    ###
    saveEnabledStateById: (triggerId, isEnabled) ->
      if isEnabled
        return @Api.sendPost("/ticket_triggers/#{triggerId}/enable")
      else
        return @Api.sendPost("/ticket_triggers/#{triggerId}/disable")


    ###
      # Deletes a trigger
      #
      # @param {Integer} triggerId
      # @return {promise}
    ###
    deleteTriggerById: (triggerId) ->
      @removeListModelById(triggerId)
      @Api.sendDelete('/ticket_triggers/' + triggerId)

    ###
      # Save order of triggers
      #
      # @param {Array} orders Array of IDs, in order
      # @return {promise}
    ###
    saveRunOrder: (orders) ->
      for id, idx in orders
        model = @findListModelById(id)
        if model
          model.display_order = idx

      promise = @Api.sendPostJson('/ticket_triggers/run_order', { run_orders: orders })
      return promise


    ###
      # Save the enabled state of a trigger
      #
      # @param {Integer} triggerId
      # @param {bool} isEnabled
      # @return {promise}
    ###
    saveGroupEnabledState: (type, isEnabled) ->
      verb = if isEnabled then 'enable' else 'disable'

      if type == 'departments' and @type == 'newticket'
        promise = @Api.sendPost("/ticket_triggers/departments/#{verb}")
        @department_triggers_enabled = isEnabled
      if type == 'departments' and @type == 'update'
        promise = @Api.sendPost("/ticket_triggers/departments_changed/#{verb}")
        @department_triggers_enabled = isEnabled
      if type == 'email_accounts' and @type == 'newticket'
        promise = @Api.sendPost("/ticket_triggers/email_accounts/#{verb}")
        @emailaccount_triggers_enabled = isEnabled

      return promise