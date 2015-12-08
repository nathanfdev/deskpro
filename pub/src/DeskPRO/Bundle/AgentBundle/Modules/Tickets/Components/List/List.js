import React, { Component, PropTypes } from 'react';
import { ListFrameContainer, ListFrameContents } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { ControlBarContainer } from './ControlBarContainer';
import { ListTableViewContainer } from './View/Table/ListTableViewContainer';
import { ListCardViewContainer } from './View/Card/ListCardViewContainer';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameMenu';
import { toggleAll} from '../../Actions/listActions';

export class List extends Component {
  static propTypes = {
    selectedCount: PropTypes.number.isRequired,
    viewMode: PropTypes.string.isRequired,
    isDone: PropTypes.bool.isRequired
  };

  render() {
    const checkbox = { count: this.props.selectedCount, action: toggleAll };

    return (
      <ListFrameContainer>
        <ListFrameMenu checkbox={checkbox}>
          <ControlBarContainer />
        </ListFrameMenu>
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
