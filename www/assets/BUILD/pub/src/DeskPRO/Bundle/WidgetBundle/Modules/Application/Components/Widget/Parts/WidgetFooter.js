import React from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class WidgetFooter extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-powered-by-deskpro">
        <a href="https://www.deskpro.com/" target="_blank">
          <hr/><div dangerouslySetInnerHTML={portalPhrases.getHtml('portal.chat.support_powered_by', {}, {'{DeskPRO}': '<span class="dpdesignportal-deskpro-mark-logo"></span>'})} /><hr/>
        </a>
      </div>
    );
  }
}
