import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';

import { NavFrame, NavFrameHeaderContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';

import { WidgetNavListContainer } from "./WidgetNavList";

export class NavContainer extends Component {
  render() {
    return (
      <NavFrame>
        <NavFrameHeaderContainer icon="icon-dp-streamline-hand-like-2">Example</NavFrameHeaderContainer>
        <WidgetNavListContainer />
      </NavFrame>
    );
  }
}
