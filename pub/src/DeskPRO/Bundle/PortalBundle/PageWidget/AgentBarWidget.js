import PageWidget from "DeskPRO/Component/PageWidget/PageWidget"
import $ from "jquery"

export default class AgentBarWidget extends PageWidget {
  renderWidget() {
    const agent_arrow = $('#agent-dropdown-arrow');
    const agent_drop = $('#agent-bar-agent-dropdown');
    const admin_arrow = $('#admin-dropdown-arrow');
    const admin_drop = $('#agent-bar-admin-dropdown');

    agent_drop.css('top', agent_arrow.offset().top + agent_arrow.height());
    agent_drop.css('right', $(document).width() - agent_arrow.offset().left - agent_arrow.width() - 25);

    admin_drop.css('top', admin_arrow.offset().top + admin_arrow.height());
    admin_drop.css('right', $(document).width() - admin_arrow.offset().left - admin_arrow.width() - 25);
  }
}
