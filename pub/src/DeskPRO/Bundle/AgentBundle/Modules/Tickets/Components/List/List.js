import React, { Component, PropTypes } from 'react';
import { ListFrameContainer, ListFrameContents } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { ControlBarContainer } from './ControlBarContainer';
import { ListTableViewContainer } from './View/Table/ListTableViewContainer';
import { ListCardViewContainer } from './View/Card/ListCardViewContainer';

export class List extends Component {
  static propTypes = {
    viewMode: PropTypes.string.isRequired,
    isDone: PropTypes.bool.isRequired
  };

  render() {
    return (
      <ListFrameContainer>
        <ControlBarContainer />
        <ListFrameContents>
          {this.renderList()}
        </ListFrameContents>
      </ListFrameContainer>
    );
  }

  renderList() {
    switch (this.props.isDone) {
      case false:
        return <div>Loading...</div>;
      case true:
        return this.props.viewMode === 'table' ? <ListTableViewContainer /> : <ListCardViewContainer />;
      default:
        return <div />;
    }
  }
}
