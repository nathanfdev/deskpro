import React, { PropTypes } from 'react';
import { TopBar, TopBarItem, TopBarRightMenu, TopBarNotificationIcon } from 'DeskPRO/Component/Semantic/TopBar';
import SearchBox from 'DeskPRO/Component/Semantic/SearchBox';
import AddButton from './AddButton';
import Chat from './Chat';
import User from './User';

class AgentTopBar extends React.Component {
  static propTypes = {
    onSearch: PropTypes.func
  };
  static defaultProps = {
    onSearch() {}
  };

  constructor() {
    super();
    this.state = {
      agents: [],
      user:   null
    };
    this.retrieveAgents = this.retrieveAgents.bind(this);
    this.getUserPicture = this.getUserPicture.bind(this);
  }

  componentWillMount() {
    this.retrieveAgents();
  }

  getUserPicture() {
    const { user } = this.state;
    if (!user) {
      return '';
    }
    let img = user.avatar.default_url_pattern;
    if (user.avatar.url_pattern) {
      img = user.avatar.url_pattern;
    }
    return img.replace(/\{\{IMG_SIZE}}/, 32);
  }

  retrieveAgents() {
    const url = `${window.BASE_URL}api/v2/agents`;
    const self = this;

    // TODO refactor
    window.$.ajax({
      url,
      type:     'GET',
      dataType: 'json',
      headers:  {
        'X-Agent-Request': true
      },
      complete(response) {
        const agents = response.responseJSON.data;
        self.setState({
          agents
        });
        for (const agent of agents) {
          if (parseInt(agent.id, 10) === window.DESKPRO_PERSON_ID) {
            self.setState({
              user: agent
            });
            break;
          }
        }
      }
    });
  }

  render() {
    const { onSearch } = this.props;
    const { agents } = this.state;
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
          <TopBarNotificationIcon elementId="notifications" icon="alarm outline" count="2" />
        </TopBarItem>
        <TopBarItem>
          <User src={this.getUserPicture()} />
          <Chat agents={agents} />
        </TopBarItem>
      </TopBarRightMenu>
    </TopBar>);
  }
}
export default AgentTopBar;
