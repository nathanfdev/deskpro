import React, { Component, PropTypes } from 'react';
import { ListFrameContainer, ListFrameContents } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { ControlBarContainer } from './ControlBarContainer';
import { ListTableViewContainer } from './View/Table/ListTableViewContainer';
import { ListCardViewContainer } from './View/Card/ListCardViewContainer';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameMenu';
import { toggleAll} from '../../Actions/listActions';
import { PaginationContainer } from './PaginationContainer';

export class List extends Component {
  static propTypes = {
    selectedCount: PropTypes.number.isRequired,
    viewMode: PropTypes.string.isRequired,
    pagination: PropTypes.object,
    isDone: PropTypes.bool.isRequired
  };

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

  render() {
    const { selectedCount, pagination } = this.props;
    const checkbox = { count: selectedCount, action: toggleAll };

    return (
      <ListFrameContainer>
        <ListFrameMenu checkbox={checkbox}>
          <ControlBarContainer />
        </ListFrameMenu>
        <ListFrameContents>
          {this.renderList()}
          {pagination && pagination.total_pages > 1 && <PaginationContainer/>}
        </ListFrameContents>
      </ListFrameContainer>
    );
  }
}
