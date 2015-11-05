import React, { Component, PropTypes } from 'react';
import { ListFrameContainer, ListFrameContents } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { ControlBarContainer } from './ControlBar/ControlBarContainer';
import { ListTableViewContainer } from './View/Table/ListTableViewContainer';
import { ListCardViewContainer } from './View/Card/ListCardViewContainer';

export class List extends Component {
  static propTypes = {
    mode: PropTypes.string.isRequired,
    isDone: PropTypes.bool.isRequired
  };

  render() {
    return (
      <ListFrameContainer>
        <ControlBarContainer />
        {this.renderList()}
      </ListFrameContainer>
    );
  }

  renderList() {
    return this.props.isDone ? (
      <ListFrameContents>
        {this.props.mode === 'table' ? <ListTableViewContainer /> : <ListCardViewContainer />}
      </ListFrameContents>
    ) : <div>Loading...</div>;
  }
}
