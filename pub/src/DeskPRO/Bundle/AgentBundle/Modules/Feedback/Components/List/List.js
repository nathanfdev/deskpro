import React, {Component, PropTypes} from 'react';
import { ListFrame }  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { FeedbackListControlBar } from './ControlBar/FeedbackListControlBar';
import { FeedbackList } from './View/List/FeedbackList';
import { FeedbackTable } from './View/Table/FeedbackTable';

import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'

export class List extends Component {

  static propTypes = {
    elements: PropTypes.array.isRequired,
    currentViewMode: PropTypes.object.isRequired
  };


  render() {

    const { elements, currentViewMode } = this.props;
    return (
      <ListFrame>
        <FeedbackListControlBar />
        {currentViewMode.field === constants.VIEW_MODE_LIST ?
         <FeedbackList elements={elements}/> : <FeedbackTable elements={elements}/>}
      </ListFrame>
    );
  }

}
