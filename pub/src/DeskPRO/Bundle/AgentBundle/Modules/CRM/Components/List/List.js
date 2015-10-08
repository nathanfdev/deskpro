import React, {Component, PropTypes} from 'react';
import { ListFrameContainer, ControlBar }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { CrmListControlBar } from './ControlBar/CrmListControlBar';
import { CrmList } from './View/List/CrmList';
import { CrmTable } from './View/Table/CrmTable';

import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'

export class List extends Component {

  constructor(props) {
    super(props);
  }

  static propTypes = {
    elements: PropTypes.array.isRequired,
    viewModeOptions: PropTypes.array.isRequired
  };


  render() {

    const { elements, viewModeOptions } = this.props;

    return (
      <ListFrameContainer>
        <CrmListControlBar />
        {viewModeOptions.find((option)=>option.current === true).field === constants.VIEW_MODE_LIST ? <CrmList elements={elements}/> : <CrmTable elements={elements}/>}
      </ListFrameContainer>
    );
  }

}
