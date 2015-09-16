import React, {Component, PropTypes} from 'react';
import { ListFrame }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { FeedbackListControlBar } from './ControlBar/FeedbackListControlBar';
import { FeedbackList } from './View/List/FeedbackList';
import { FeedbackTable } from './View/Table/FeedbackTable';

import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'

export class List extends Component {

  static propTypes = {
    elements: PropTypes.array.isRequired,
    viewModeOptions: PropTypes.array.isRequired
  };


  render() {

    const { elements, viewModeOptions } = this.props;

    return (
      <ListFrame>
        <FeedbackListControlBar />
        {viewModeOptions.find((option)=>option.current === true).field === constants.VIEW_MODE_LIST ?
         <FeedbackList elements={elements}/> : <FeedbackTable elements={elements}/>}
      </ListFrame>
    );
  }

}
