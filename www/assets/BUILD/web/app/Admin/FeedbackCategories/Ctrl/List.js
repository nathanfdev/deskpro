define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_FeedbackCategories_Ctrl_List extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_FeedbackCategories_Ctrl_List'
    @CTRL_AS = 'FeedbackCategoriesList'
    @DEPS    = ['$rootScope', '$scope', 'FeedbackCategoriesData', 'em', 'Api', '$state', 'Growl']

    init: ->

      @$scope.brand_id = @$stateParams.brandId
      @feedback_categories = []
      @parent_data = []
      @child_data = {}

      @sortedListOptions = {

        axis: 'y',
        handle: '.drag-handle',
        update: (ev, data) =>
          $list = data.item.closest('ul')

          postData = {display_orders: []}

          x = 0
          em = @em

          $list.find('li').each(->

            x += 10
            feedback_category_id = parseInt($(this).data('id'))

            if feedback_category_id

              feedback_category = em.getById('feedback_category', feedback_category_id)

              if feedback_category
                feedback_category.display_order = x

            postData.display_orders.push(feedback_category_id)
          )

          @Api.sendPostJson('/feedback_categories/display_order', postData)
          @pingElement('display_orders')
      }

    sort: (values) ->
      (values || []).sort (a, b) =>
        orderA = parseInt(a.display_order)
        orderB = parseInt(b.display_order)
        return -1 if orderA < orderB
        return 1 if orderA > orderB
        return 0

    initialLoad: ->

      promises = []
      promises.push @FeedbackCategoriesData.loadList().then( (recs) =>

        @initHierarchyData(@sort recs.values())

        @addManagedListener(@FeedbackCategoriesData.recs, 'changed', =>

          @initHierarchyData(@sort @FeedbackCategoriesData.recs.values())
          @ngApply()
        )
      )

      return @$q.all(promises)

    initHierarchyData: (feedback_categories) ->

      @feedback_categories = feedback_categories
      @parent_data = []
      @child_data = {}

      for category in feedback_categories

        if parseInt(category.parent_id, 10)

          if not @child_data[category.parent_id]
            @child_data[category.parent_id] = []

          @child_data[category.parent_id].push(category)

        else

          @parent_data.push(category)


  Admin_FeedbackCategories_Ctrl_List.EXPORT_CTRL()