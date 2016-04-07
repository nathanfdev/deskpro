import React from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class WidgetFooter extends React.Component {

  render() {
      // portal.general.support_powered_by
    return (
      <div className="dpdesignportal-powered-by-deskpro">
        <a href="https://www.deskpro.com/" target="_blank">
          <hr/> Support powered by <span className="dpdesignportal-deskpro-mark-logo"></span> <hr/>
        </a>
      </div>
    );
  }
}
