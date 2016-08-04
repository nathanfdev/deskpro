import React, { PropTypes } from 'react';
import { TopBar, TopBarItem, TopBarRightMenu, TopBarNotificationIcon } from 'DeskPRO/Component/Semantic/TopBar';
import SearchBox from 'DeskPRO/Component/Semantic/SearchBox';
import AddButton from './AddButton';
import Chat from './Chat';
import User from './User';

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
      <div className="logo">
        <img src="/assets/BUILD/web/images/dp-logo-48.png" alt="DeskPRO logo" />
      </div>
      <SearchBox onUserInput={onSearch} placeholder="Search ..." />
      <TopBarItem>
        <i className="icon wait" />
      </TopBarItem>
      <AddButton />
      <TopBarRightMenu>
        <TopBarItem>
          <TopBarNotificationIcon icon="alarm outline" count="2" />
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
