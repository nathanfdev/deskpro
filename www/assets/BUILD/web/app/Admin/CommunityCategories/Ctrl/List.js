define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_CommunityCategories_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_CommunityCategories_Ctrl_List';
      this.CTRL_AS = 'CommunityCategoriesList';
      this.DEPS    = ['$rootScope', '$scope', 'CommunityCategoriesData', 'em', 'Api', '$state', 'Growl'];
    }

    init() {
      this.$scope.brand_id = this.$stateParams.brandId;
      this.community_categories = [];
      this.parent_data = [];
      this.child_data = {};

      return this.sortedListOptions = {

        axis:   'y',
        handle: '.drag-handle',
        update: (ev, data) => {
          const $list = data.item.closest('ul');

          const postData = { display_orders: [] };

          let x = 0;
          const { em } = this;

          $list.find('li').each(function () {
            x += 10;
            const community_category_id = parseInt($(this).data('id'));

            if (community_category_id) {
              const community_category = em.getById('community_category', community_category_id);

              if (community_category) {
                community_category.display_order = x;
              }
            }

            return postData.display_orders.push(community_category_id);
          });

          this.Api.sendPostJson('/community_categories/display_order', postData);
          return this.pingElement('display_orders');
        }
      };
    }

    sort(values) {
      return (values || []).sort((a, b) => {
        const orderA = parseInt(a.display_order);
        const orderB = parseInt(b.display_order);
        if (orderA < orderB) { return -1; }
        if (orderA > orderB) { return 1; }
        return 0;
      });
    }

    initialLoad() {
      const promises = [];
      promises.push(this.CommunityCategoriesData.loadList().then((recs) => {
        this.initHierarchyData(this.sort(recs.values()));

        return this.addManagedListener(this.CommunityCategoriesData.recs, 'changed', () => {
          this.initHierarchyData(this.sort(this.CommunityCategoriesData.recs.values()));
          return this.ngApply();
        });
      })
      );

      return this.$q.all(promises);
    }

    initHierarchyData(community_categories) {
      this.community_categories = community_categories;
      this.parent_data = [];
      this.child_data = {};

      return (() => {
        const result = [];
        for (const category of Array.from(community_categories)) {
          if (parseInt(category.parent_id, 10)) {
            if (!this.child_data[category.parent_id]) {
              this.child_data[category.parent_id] = [];
            }

            result.push(this.child_data[category.parent_id].push(category));
          } else {
            result.push(this.parent_data.push(category));
          }
        }
        return result;
      })();
    }
  }
  Admin_CommunityCategories_Ctrl_List.initClass();


  return Admin_CommunityCategories_Ctrl_List.EXPORT_CTRL();
});
