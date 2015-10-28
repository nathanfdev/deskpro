import React, { Component, PropTypes } from 'react';
import { NavFrame, NavFrameHeader, NavFrameBody, TabsPane, Tab }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { FiltersTabContainer, LabelsTabContainer, StarsTabContainer } from './Tabs/index';
import { FilterEditPopupContainer } from './FilterEditPopupContainer';

export class Nav extends Component {
  render() {
    return (
      <NavFrame>
        <div part="outer">
          <FilterEditPopupContainer />
        </div>
        <div part="inner">
          <NavFrameHeader icon="icon icon-dp-streamline-mail-2">Tickets</NavFrameHeader>
          <NavFrameBody>
            <TabsPane>
              <Tab title="Filters">
                <FiltersTabContainer />
              </Tab>
              <Tab title="Labels">
                <LabelsTabContainer />
              </Tab>
              <Tab title="Stars">
                <StarsTabContainer />
              </Tab>
            </TabsPane>
          </NavFrameBody>
        </div>
      </NavFrame>
    );
  }
}
