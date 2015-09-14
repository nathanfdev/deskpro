import React, {Component, PropTypes} from 'react';
import { ListFrame,  Pagination }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { FeedbackListControlBar } from './ControlBar/FeedbackListControlBar';
import { FeedbackList } from './View/List/FeedbackList';
import { FeedbackTable } from './View/Table/FeedbackTable';

import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'

export class List extends Component {

  static propTypes = {
    elements: PropTypes.array.isRequired,
    viewMode: PropTypes.string.isRequired
  };


  render() {

    const { elements, viewMode } = this.props;

    return (
      <ListFrame>
        <FeedbackListControlBar />
        {viewMode === constants.VIEW_MODE_LIST ? <FeedbackList elements={elements}/> : <FeedbackTable elements={elements}/>}
        <Pagination/>
      </ListFrame>
    );
  }

}
