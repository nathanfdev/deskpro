import React, {Component, PropTypes} from 'react';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameMenu';
import { CrmList } from './View/List/CrmList';
import { CrmTable } from './View/Table/CrmTable';
import { ControlBarContainer } from './ControlBar/ControlBarContainer';
import { MassActionContainer } from './ControlBar/MassActionContainer';

import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

export class List extends Component {

  static propTypes = {
    elements: PropTypes.array.isRequired,
    selected: PropTypes.object.isRequired,
    viewModeOptions: PropTypes.array.isRequired
  };

  constructor(props) {
    super(props);
  }

  render() {
    const { elements, viewModeOptions, selected } = this.props;
    const checkbox = {
      count: selected.size, action: ()=> {
      }
    };

    return (
      <ListFrameContainer>
        <ListFrameMenu checkbox={checkbox}>
          {!selected.size && <ControlBarContainer key="1"/>}
          {selected.size && <MassActionContainer key="2"/>}
        </ListFrameMenu>
        {viewModeOptions.find((option)=>option.current === true).field === constants.VIEW_MODE_CARD ? <CrmList elements={elements}/> : <CrmTable elements={elements}/>}
      </ListFrameContainer>
    );
  }

}
