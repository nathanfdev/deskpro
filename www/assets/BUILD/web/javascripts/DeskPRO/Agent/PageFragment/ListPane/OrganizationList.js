Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.OrganizationList = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'org-list';
		this.wrapper = null;
		this.contentWrapper = null;
		this.overlay = null;
		this.appendUrl = null;
		this.actionsBarHelper = null;
		this.resultTypeName = 'filter';
		this.resultTypeId = 0;
	},

	initPage: function(el) {

		var self = this;

		this.wrapper = $(el);
		this.contentWrapper = $('div.content:first', this.wrapper);
        this.fixed_fields = ['id', 'name'];

		DeskPRO_Window.ngModule.dpInjector.invoke(['$compile', '$rootScope', '$q', '$timeout', '$http', function($compile, $rootScope, $q, $timeout, $http) {
			self.$scope = $rootScope.$new();

			self.$scope.$safeApply = (function(fn) {
				var phase = this.$root.$$phase;
				if(phase == '$apply' || phase == '$digest') {
					if(fn && (typeof(fn) === 'function')) {
						fn();
					}
				} else {
					this.$apply(fn);
				}
			}).bind(self.$scope);

			self.$q = $q;
			self.$timeout = $timeout;
			self.$http = $http;

			self.wrapper.data('$ngControllerController', self);
			$compile(self.wrapper.contents())(self.$scope);

			self.initScope();
		}]);

		this.resultTypeId = this.meta.cache_id || 0;

		if (this.getMetaData('noResults')) {
			this.noMoreResults = true;
			$('.no-more-results', this.contentWrapper).show();
		}

		this.displayOptions = new DeskPRO.Agent.PageHelper.DisplayOptions(this, {
			prefId: 'org-filter',
			resultId: this.resultId,
			refreshUrl: this.meta.refreshUrl
		});
		this.ownObject(this.displayOptions);

		this.enableHighlightOpenRows('organization', 'org_id', 'article.org-');

		// Sorting options
		var sortMenuBtn = $('.order-by-menu-trigger', this.wrapper).first();
		this.sortingMenu = new DeskPRO.UI.Menu({
			triggerElement: sortMenuBtn,
			menuElement: $('.order-by-menu', this.wrapper).first(),
			onItemClicked: function(info) {
				var item = $(info.itemEl);

				var prop = item.data('order-by');
				var label = $.trim(item.text());

				// Change the displayed label for some visual feedback
				$('.label', sortMenuBtn).text(label);

				var disOptWrap = self.displayOptions.getWrapperElement();
				var sel = $('select.sel-order-by', disOptWrap);
				$('option', sel).prop('selected', false);
				$('option.' + prop, sel).prop('selected', true);

				self.displayOptions.saveAndRefresh();
			}
		});
		this.ownObject(this.sortingMenu);
		this.addEvent('activate', this.fillListItems, this);
	},

	initScope: function(){
		var self = this,
			$scope = self.$scope;

		$scope.organizations = this.meta.organizations;
		$scope.displayFields = this.meta.displayFields;
        $scope.listType = 'list';
        $scope.switchViewType = function() {
            $scope.listType = 'list' === $scope.listType ? 'table' : 'list';
        };

		$scope.isFieldDisplayable = function(org, field) {
			switch (field) {
				case 'members_count':
					return org.members_count === undefined ? false : true;
				case 'labels':
					return org.labels && org.labels.length > 0;
				default:
					if (0 !== field.indexOf('organization_fields')) return false;
					return !!org[field];
			}
		};

        $scope.getDisplayableFields = function() {
            var fields = [];
            self.fixed_fields.forEach(function(v){
                fields.push(v);
            });
            $scope.displayFields.forEach(function(v){
                if (fields.indexOf(v) > -1) return;
                fields.push(v);
            });
            return fields;
        };

        $scope.getFieldDisplayName = function(field){
            return (field.charAt(0).toUpperCase() + field.slice(1)).replace('_', ' ');
        };

		$scope.$watch('organizations', this.fillListItems.bind(this));

		// sometimes $scope.persons won't apply (as we're working outside of digest loop most of time), so force it
		$scope.$safeApply();
	},

	fillListItems: function() {
		var self = this,
			$scope = this.$scope,
			routeTemplate = $scope.routes.organization;

		if (!self.IS_ACTIVE) return;
		$scope.listItems.length = 0;

		$scope.organizations.forEach(function(org){
			$scope.addListItem('organization', 'organization:'+org.id, org.name, routeTemplate.replace('0000', org.id));
		});
	},
});
