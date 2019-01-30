define [
  'Admin/Main/DataService/BaseListEdit',
], (
  BaseListEdit
)  ->
  class Admin_CustomFields_DataService_CustomFields extends BaseListEdit
    @$inject = ['Api', '$q']



    init: (owner, context) ->
      @_params = {owner: owner || null, context: context || null}



    url: ->
      '/custom_fields'



    _doLoadList: ->
      deferred = @$q.defer()

      @Api.sendGet(@url(), @_params).then (res) ->
        deferred.resolve res.data || []

      deferred.promise



    ###
    # Update display orders
    #
    # @param {Array} Array of IDs in order
    # @return {promise}
    ###
    saveDisplayOrder: (display_orders) ->
      @Api.sendPostJson('/custom_fields/display-order', {display_orders: display_orders})

