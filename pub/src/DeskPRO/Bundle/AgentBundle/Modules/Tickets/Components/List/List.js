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
    pagination: PropTypes.object,
    isLoaded: PropTypes.bool.isRequired
  };

  render() {
    const { isLoaded, pagination } = this.props;

    return (
      <ListFrameContainer>
        <ListFrameMenu>
          <ControlBarContainer />
        </ListFrameMenu>
        <ListFrameContents isLoaded={isLoaded}>
          {this.props.viewMode === 'table' ? <ListTableViewContainer /> : <ListCardViewContainer />}
          {pagination && pagination.total_pages > 1 && <PaginationContainer/>}
        </ListFrameContents>
      </ListFrameContainer>
    );
  }
}
