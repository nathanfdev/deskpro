import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { getDrafts, updateDrafts } from './DpxFormDraft';

export class DpxFormClearDraft extends PageWidget {

  renderWidget() {
    const formName = this.$element.data('save-draft');
    const drafts = getDrafts();
    drafts[formName] = {};

    updateDrafts(drafts);
  }
}
