define([
  'angular',
  'Admin/CustomFields/Download/CategoryBuilder/Controller'
], (
  angular,
  DeskPRO_Download_Eula_CategoryBuilder_Controller
) =>
  angular.module('deskpro.custom_filed_download_eula_category_builder', [])
    .directive('dpCustomFieldDownloadEulaCategoryBuilder', [() =>
      ({
        restrict: 'E',
        require:  'ngModel',
        template: `\
<div class="dp-category-builder">
  <ul class="dp-cb-root" ui-sortable="sortedListOptions"></ul>
  <div class="dp-cb-newrow">
    <input type="text" class="form-control" ng-model="new_cat_title" placeholder="Enter a title..." />
    <span class="dp-cb-select-wrap" style="display:none">
      <select ng-model="new_cat_parent"
        ui-select2
        style="min-width:200px;"
      >
        <option value="0">No parent</option>
        <option value="{{c.id}}" ng-repeat="c in parent_cat_list">{{c.title}}</option>
      </select>
    </span>
    <button class="btn dp-cb-addbtn">Add</button>
  </div>
</div>\
`,
        replace:      true,
        controller:   DeskPRO_Download_Eula_CategoryBuilder_Controller.FACTORY,
        controllerAs: 'CategoryBuilder',
        scope:        {
          saveFlatArray: '=',
          fieldName:     '@fieldName',
          fieldType:     '=',
          editClickCallback: "&"
        },
        link(scope, iElement, iAttrs, ngModel) {
          return scope.categoryBuilder.setModel(ngModel);
        }
      })

    ])
);
