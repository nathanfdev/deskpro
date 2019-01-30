/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_ApiKeys_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_ApiKeys_Ctrl_List';
      this.CTRL_AS = 'ListCtrl';
    }

    init() {
      return this.service = this.DataService.get('ApiKeys');
    }



    /*
     * Loads the list
     */
    initialLoad() {
      return this.service.all().then(list => { return this.list = list; });
    }
  }
  Admin_ApiKeys_Ctrl_List.initClass();



  return Admin_ApiKeys_Ctrl_List.EXPORT_CTRL();
});