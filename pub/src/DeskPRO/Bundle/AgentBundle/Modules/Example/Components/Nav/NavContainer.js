import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';

import { NavFrame, NavFrameHeader } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';

import { WidgetNavListContainer } from "./WidgetNavList";

export class NavContainer extends Component {
  render() {
    return (
      <NavFrame>
        <NavFrameHeader icon="icon-dp-streamline-hand-like-2">
          Example
        </NavFrameHeader>

        <WidgetNavListContainer />
      </NavFrame>
    );
  }
}
