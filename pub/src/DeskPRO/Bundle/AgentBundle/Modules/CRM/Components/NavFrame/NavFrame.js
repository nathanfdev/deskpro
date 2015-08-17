import React from 'react';
import { NavFrame as BaseNavFrame } from 'DeskPRO/Bundle/Agentbundle/Modules/Application/Components/NavFrame/NavFrame';
import { NavFrameHeader } from 'DeskPRO/Bundle/Agentbundle/Modules/Application/Components/NavFrame/NavFrameHeader';

export class NavFrame extends React.Component {
  render() {
    return (
      <BaseNavFrame>

        <div part="outer"></div>

        <div part="inner">
          <NavFrameHeader icon="fa-users">CRM</NavFrameHeader>

          <div className="sidebar-list sidebar-list-filters">
            test
          </div>
        </div>

      </BaseNavFrame>
    );
  }
}
