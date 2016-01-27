import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import $ from 'jquery';

export class AgentBarWidget extends PageWidget {

  renderWidget() {
    const agentArrow = $('#agent-dropdown-arrow');
    const agentDrop = $('#agent-bar-agent-dropdown');
    const adminArrow = $('#admin-dropdown-arrow');
    const adminDrop = $('#agent-bar-admin-dropdown');

    agentDrop.css('top', agentArrow.offset().top + agentArrow.height());
    agentDrop.css('right', $(document).width() - agentArrow.offset().left - agentArrow.width() - 25);

    adminDrop.css('top', adminArrow.offset().top + adminArrow.height());
    adminDrop.css('right', $(document).width() - adminArrow.offset().left - adminArrow.width() - 25);
  }
}
