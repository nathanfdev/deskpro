import React, { PropTypes } from 'react';
import { TopBar, TopBarItem, TopBarRightMenu, TopBarNotificationIcon } from 'DeskPRO/Component/Semantic/TopBar';
import SearchBox from 'DeskPRO/Component/Semantic/SearchBox';
import User from './User/User';
import Chat from './Chat/Chat';

class AgentTopBar extends React.Component {
  static propTypes = {
    agents:   PropTypes.array,
    onSearch: PropTypes.func
  };
  static defaultProps = {
    onSearch() {}
  };


  render() {
    const { agents, onSearch } = this.props;
    return (<TopBar>
      <SearchBox onUserInput={onSearch} placeholder="Search" />
      <TopBarItem>
        <i className="icon wait" />
      </TopBarItem>
      <TopBarRightMenu>
        <TopBarItem>
          <TopBarNotificationIcon icon="bell" count="2" />
        </TopBarItem>
        <TopBarItem>
          <User src="teleoperator.jpg" />
          <Chat onlineAgents={agents} />
        </TopBarItem>
      </TopBarRightMenu>
    </TopBar>);
  }
}
export default AgentTopBar;
