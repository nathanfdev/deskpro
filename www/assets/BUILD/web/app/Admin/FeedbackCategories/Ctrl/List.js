// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS205: Consider reworking code to avoid use of IIFEs
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_FeedbackCategories_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_FeedbackCategories_Ctrl_List';
      this.CTRL_AS = 'FeedbackCategoriesList';
      this.DEPS    = ['$rootScope', '$scope', 'FeedbackCategoriesData', 'em', 'Api', '$state', 'Growl'];
    }

    init() {

      this.$scope.brand_id = this.$stateParams.brandId;
      this.feedback_categories = [];
      this.parent_data = [];
      this.child_data = {};

      return this.sortedListOptions = {

        axis: 'y',
        handle: '.drag-handle',
        update: (ev, data) => {
          const $list = data.item.closest('ul');

          const postData = {display_orders: []};

          let x = 0;
          const { em } = this;

          $list.find('li').each(function() {

            x += 10;
            const feedback_category_id = parseInt($(this).data('id'));

            if (feedback_category_id) {

              const feedback_category = em.getById('feedback_category', feedback_category_id);

              if (feedback_category) {
                feedback_category.display_order = x;
              }
            }

            return postData.display_orders.push(feedback_category_id);
          });

          this.Api.sendPostJson('/feedback_categories/display_order', postData);
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
      promises.push(this.FeedbackCategoriesData.loadList().then( recs => {

        this.initHierarchyData(this.sort(recs.values()));

        return this.addManagedListener(this.FeedbackCategoriesData.recs, 'changed', () => {

          this.initHierarchyData(this.sort(this.FeedbackCategoriesData.recs.values()));
          return this.ngApply();
        });
      })
      );

      return this.$q.all(promises);
    }

    initHierarchyData(feedback_categories) {

      this.feedback_categories = feedback_categories;
      this.parent_data = [];
      this.child_data = {};

      return (() => {
        const result = [];
        for (let category of Array.from(feedback_categories)) {

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
  Admin_FeedbackCategories_Ctrl_List.initClass();


  return Admin_FeedbackCategories_Ctrl_List.EXPORT_CTRL();
});