define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Arrays'], (Admin_Ctrl_Base, Arrays) ->
  class Admin_TicketFields_Ctrl_EditProducts extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_TicketFields_Ctrl_EditProducts'
    @CTRL_AS = 'TicketProds'
    @DEPS    = []

    init: ->
      @products         = []
      @default_id       = 0
      @agent_required   = false
      @user_required    = false
      @cat_parent_list  = []

      @$scope.$watchCollection('TicketProds.products', =>
        @updateCatParentList()
      , true)
      return

    updateCatParentList: ->
      @cat_parent_list = []

      flat = Arrays.analyzeFlatCatStructure(@products)
      valid_ids = []
      for cat in flat
        if not cat.child_ids.length
          valid_ids.push(cat.id)
          @cat_parent_list.push({
            id: cat.id,
            title: cat.full_title
          })

      if valid_ids.indexOf(@default_id) == -1
        @default_id = 0

    initialLoad: ->
      data_promise = @Api.sendDataGet({
        'info': '/ticket_prods',
        'layouts': '/ticket_layouts/fields/product'
      }).then( (res) =>
        @products       = res.data.info.products
        @default_id     = res.data.info.default_id + ""
        @agent_required = res.data.info.agent_required
        @user_required  = res.data.info.user_required
        @enabled        = res.data.info.enabled

        @user_layouts  = res.data.layouts.user_layouts
        @agent_layouts = res.data.layouts.agent_layouts

        @updateCatParentList()
      )

      return data_promise

    save: ->
      if not @products or not @products.length
        @enabled = false

      postData = {
        products:       @products,
        default_id:     @default_id,
        user_required:  @user_required,
        agent_required: @agent_required,
        enabled:        @enabled
      }

      @startSpinner('saving')
      promise = @Api.sendPostJson('/ticket_prods', postData).success( =>
        @$scope.$parent?.TicketFieldsList?.saveLayoutData('product', @user_layouts, @agent_layouts)
        @$scope.$parent?.TicketFieldsList?.setFieldEnabled('product', @enabled)
        @settings = angular.copy(@$scope.settings)

        @stopSpinner('saving').then(=>
          @Growl.success(@getRegisteredMessage('saved_settings'))
        )
      ).error( (info, code) =>
        @stopSpinner('saving', true)
        @applyErrorResponseToView(info)
      )

  Admin_TicketFields_Ctrl_EditProducts.EXPORT_CTRL()