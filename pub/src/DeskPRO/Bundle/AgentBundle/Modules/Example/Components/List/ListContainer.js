import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';

import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { ListFrameContents } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameContents';

import { BlankList } from './BlankList';
import { WidgetListContainer } from './WidgetsList';

@connect(state => ({
  navItem: state.Example.app.get('navItem')
}))
export class ListContainer extends Component {
  render() {
    const { navItem } = this.props;

    if (!navItem || !navItem.get('type')) {
      return (<BlankList />);
    } else if (navItem.get('type') == 'widgetType') {
      return (
        <WidgetListContainer widgetType={navItem.getIn(['params', 'widgetType'])} />
      );
    } else {
      return (<div>Unknown type: {navItem.get('type', 'none')}</div>);
    }
  }
}
