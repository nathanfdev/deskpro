import React, {Component, PropTypes} from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameMenu';
import { ListFrameContents } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameContents';
import { CrmCardContainer } from './View/List/CrmCardContainer';
import { CrmTableContainer } from './View/Table/CrmTableContainer';
import { ControlBarContainer } from './ControlBar/ControlBarContainer';
import { MassActionContainer } from './ControlBar/MassActionContainer';
import { PaginationContainer } from './PaginationContainer';

export class List extends Component {

  static propTypes = {
    selected: PropTypes.object.isRequired,
    loaded: PropTypes.bool.isRequired,
    pagination: PropTypes.object,
    currentViewMode: PropTypes.string.isRequired,
    content: PropTypes.string.isRequired
  };

  render() {
    const { currentViewMode, selected, loaded, pagination, content } = this.props;

    return (
      <ListFrameContainer>
        <ListFrameMenu>
          {!selected.size && <ControlBarContainer key="1"/>}
          {selected.size && <MassActionContainer key="2"/>}
        </ListFrameMenu>
        <LoadIndicator loaded={loaded}
                       opacity={0}
                       width={3}>
          <ListFrameContents>
            {currentViewMode === constants.VIEW_MODE_CARD ?
              <CrmCardContainer content={content}/> :
              <CrmTableContainer content={content}/>}
            {pagination && pagination.total_pages > 1 && <PaginationContainer/>}
          </ListFrameContents>
        </LoadIndicator>
      </ListFrameContainer>
    );
  }

}
