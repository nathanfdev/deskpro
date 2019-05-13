define([
  'DeskPRO/CategoryBuilder/Controller',
], (
  DeskPRO_CategoryBuilder_Controller_Base
) => {
  class DeskPRO_Download_Eula_CategoryBuilder_Controller extends DeskPRO_CategoryBuilder_Controller_Base {

    renderRow(cat) {
      const tpl = `\
<li class="dp-cb-row" data-cat-id="{{cat.id}}">
<div class="dp-cb-titlewrap" style="padding-left: {{ 33 + cat.depth * 15 }}px;">
  <div class="dp-cb-row-move"><i class="fa fa-bars"></i></div>
  <div class="dp-cb-row-controls">
    <button class="btn btn-default btn-xs" ng-click="editClickCallback({event: $event, category: cat})">Edit EULA</button>
    <i class="fa fa-times-circle remove-trigger"></i>
  </div>
  <div class="dp-cb-row-indent" style="padding-right: 0px; width: {{ cat.depth * 15 }}px;"></div>
  <span class="title-id" title="ID" ng-if="cat.id && !cat['@is_new']">#<span ng-bind="cat.id"></span></span>
  <span class="title-id" title="ID will be generated after you save" ng-if="cat['@is_new']">?</span>
  <input type="text" name="{{ fieldName }}" class="form-control dp-cb-input" ng-model="cat.title" placeholder="Enter title..." style="width:80%"/>
</div>
<ul ui-sortable="sortedListOptions"></ul>
</li>\
`;

      const rowScope = this.$scope.$new();
      rowScope.sortedListOptions = this.$scope.sortedListOptions;
      rowScope.cat = cat;
      rowScope.fieldName = cat.field_name || rowScope.fieldName;
      rowScope.editClickCallback = this.$scope.editClickCallback;
      const newRow = this.$compile(tpl)(rowScope);
      newRow.data('catId', cat.id);
      return [newRow, rowScope];
    }

  }
  DeskPRO_Download_Eula_CategoryBuilder_Controller.initClass();
  return DeskPRO_Download_Eula_CategoryBuilder_Controller;
});
