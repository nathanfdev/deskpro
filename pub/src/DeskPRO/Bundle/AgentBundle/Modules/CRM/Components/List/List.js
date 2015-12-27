import React, {Component, PropTypes} from 'react';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameMenu';
import { ListFrameContents } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameContents';
import { CrmCardContainer } from './View/List/CrmCardContainer';
import { CrmTableContainer } from './View/Table/CrmTableContainer';
import { ControlBarContainer } from './ControlBar/ControlBarContainer';
import { MassActionContainer } from './ControlBar/MassActionContainer';
import { PaginationContainer } from './PaginationContainer';

import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

export class List extends Component {

  static propTypes = {
    selected: PropTypes.object.isRequired,
    loaded: PropTypes.bool.isRequired,
    pagination: PropTypes.object,
    currentViewMode: PropTypes.string.isRequired
  };

  constructor(props) {
    super(props);
  }

  render() {
    const { currentViewMode, selected, loaded, pagination } = this.props;
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
        <LoadIndicator loaded={loaded}
                       opacity={0}
                       width={3}>
          <ListFrameContents>
            {currentViewMode === constants.VIEW_MODE_CARD ? <CrmCardContainer/> : <CrmTableContainer/>}
            {pagination && pagination.total_pages > 1 && <PaginationContainer/>}
          </ListFrameContents>
        </LoadIndicator>
      </ListFrameContainer>
    );
  }

}
