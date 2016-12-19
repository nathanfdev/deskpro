import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import $ from 'jquery';

class AgentBarWidget extends PageWidget {

  renderWidget() {
    const $agentDrop = $('#agent-bar-agent-dropdown');
    const $adminDrop = $('#agent-bar-admin-dropdown');

    // if we are inside the iframe then just change or close the iframe
    if (window.parent && window.parent.DP_FRAME_OVERLAYS && window.parent.DP_FRAME_OVERLAYS.user) {
      if ($agentDrop) {
        $agentDrop.find('a').on('click', event => {
          event.preventDefault();
          window.parent.DP_FRAME_OVERLAYS.user.close();
          window.parent.location.hash = $(event.currentTarget).attr('href').replace(/\/agent\//, '');
          window.parent.DeskPRO_Window.enableHashPath();
        });
      }

      if ($adminDrop) {
        $adminDrop.find('a').on('click', event => {
          event.preventDefault();
          window.parent.DP_FRAME_OVERLAYS.user.close();
          window.parent.DP_FRAME_OVERLAYS.admin.open($(event.currentTarget).attr('href'));
        });
      }
    }
  }
}

export default AgentBarWidget;
