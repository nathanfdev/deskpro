import React, { Component, PropTypes } from 'react';
import { ListFrameContainer, ListFrameContents } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { ControlBarContainer } from './ControlBarContainer';
import { ListTableViewContainer } from './View/Table/ListTableViewContainer';
import { ListCardViewContainer } from './View/Card/ListCardViewContainer';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameMenu';
import { PaginationContainer } from './PaginationContainer';

export class List extends Component {
  static propTypes = {
    viewMode: PropTypes.string.isRequired,
    isLoaded: PropTypes.bool.isRequired
  };

  render() {
    return (
      <ListFrameContainer>
        <ListFrameMenu>
          <ControlBarContainer />
        </ListFrameMenu>
        <ListFrameContents isLoaded={this.props.isLoaded}>
          {this.props.viewMode === 'table' ? <ListTableViewContainer /> : <ListCardViewContainer />}
          <PaginationContainer />
        </ListFrameContents>
      </ListFrameContainer>
    );
  }
}
