define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Main_Ctrl_MainPage extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Main_Ctrl_MainPage'
    @CTRL_AS   = 'Ctrl'
    @DEPS      = ['$rootScope', '$location', '$stateParams']

    init: ->
      if @$location.path() == '/license'
        @$scope.isBillingInterface = true
      else
        @$scope.isBillingInterface = false

      @$rootScope.$on('$locationChangeSuccess', =>
        if @$location.path() == '/license'
          @$scope.isBillingInterface = true
        else
          @$scope.isBillingInterface = false

        $('.dp-layout-appbody').scrollTop(0);
      )

      @settings = {
        apps_kb: true,
        apps_news: true,
        apps_downloads: true,
        apps_feedback: true,
        iface_portal: true
        portal_mode: 'publish'
      }
      @portalSettings = @DataService.get 'PortalGeneralSettings'

      @$scope.brandId = @$stateParams.brandId
      @$scope.selectBrandId = @$stateParams.brandId

      if !@$scope.brandId
        @$scope.brandId = 1

      @portalSettings.setBrandId(@$scope.brandId)

      @$scope.$watch('brand_id', =>
        @portalSettings.getSettings().then((s) => @settings = s)
      )

      @getBrands()

      @$scope.$on 'dp-update-brands', (e) =>
        @getBrands()
        @portalSettings.getSettings().then((s) => @settings = s)

      @$scope.$watch('Ctrl.portalSettings.version', =>
        @portalSettings.getSettings().then((s) => @settings = s)
      )

      return

    getBrands: ->
      @Api2.sendGet('brands').then (res) =>
        @$scope.brands = res.data.data

    changeBrand: ->
      if typeof @$stateParams.brandId != 'undefined'
        if @$scope.selectBrandId == '-1'
          @$scope.brandId = 'new';
          @$state.go 'portal.setup', {brandId: 'new'}
        else if @$scope.selectBrandId
          @$scope.brandId = @$scope.selectBrandId
          @$state.go 'portal.setup', {brandId: @$scope.selectBrandId}

  Admin_Main_Ctrl_MainPage.EXPORT_CTRL()