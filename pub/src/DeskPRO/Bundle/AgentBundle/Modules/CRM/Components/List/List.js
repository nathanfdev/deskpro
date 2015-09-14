import React, {Component, PropTypes} from 'react';
import { ListFrame, ControlBar, ListTableViewSwitcher, OrderBy, TableView, TableBody, Pagination }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
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
    viewMode: PropTypes.string.isRequired
  };


  render() {

    const { elements, viewMode } = this.props;

    return (
      <ListFrame>
        <CrmListControlBar />
        {viewMode === constants.VIEW_MODE_LIST ? <CrmList elements={elements}/> : <CrmTable elements={elements}/>}
        <Pagination/>
      </ListFrame>
    );
  }

}
