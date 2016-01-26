import React, {Component, PropTypes} from 'react';
import { NavFrame, NavFrameHeaderContainer, NavFrameBody, TabsPaneStatefulContainer, Tab }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { KBTab } from './Tabs/KBTab';
import { NewsTab } from './Tabs/NewsTab';
import { DownloadsTab } from './Tabs/DownloadsTab';
import { ToDoTab } from './Tabs/ToDoTab';

export class Nav extends Component {

  static propTypes = {
    loaded: PropTypes.bool.isRequired,
    articles: PropTypes.object.isRequired,
    news: PropTypes.object.isRequired,
    downloads: PropTypes.object.isRequired,
    todo: PropTypes.object.isRequired,
    grouping: PropTypes.object.isRequired,
    onGroupingChange: PropTypes.func.isRequired,
    setMine: PropTypes.func.isRequired
  };

  toggle(event) {
    event.preventDefault();
    this.setState({ 'expanded': !this.state.expanded });
  }

  close() {
    this.setState({ 'expanded': false });
  }

  render() {
    const { articles, news, downloads, todo, setMine, loaded } = this.props;

    return (
      <NavFrame>
        <NavFrameHeaderContainer icon="icon-dp-streamline-edit-1">Publish</NavFrameHeaderContainer>
        <NavFrameBody>
          <TabsPaneStatefulContainer id="tab">
            <Tab title="KB">
              <KBTab articles={articles}
                     loaded={loaded}
                     toggleGroupingVisibility={this.toggle}
                     closeGroupingVisibility={this.close}/>
            </Tab>
            <Tab title="News">
              <NewsTab news={news}
                       loaded={loaded}
                       toggleGroupingVisibility={this.toggle}
                       closeGroupingVisibility={this.close}/>
            </Tab>
            <Tab icon="fa-download">
              <DownloadsTab downloads={downloads}
                            loaded={loaded}
                            toggleGroupingVisibility={this.toggle}
                            closeGroupingVisibility={this.close}/>
            </Tab>
            <Tab title="Todo">
              <ToDoTab todo={todo}
                       loaded={loaded}
                       setMine={setMine}/>
            </Tab>
          </TabsPaneStatefulContainer>
        </NavFrameBody>
      </NavFrame>
    );
  }
}
