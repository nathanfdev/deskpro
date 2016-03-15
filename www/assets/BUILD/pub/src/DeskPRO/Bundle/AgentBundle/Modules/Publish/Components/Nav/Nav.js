import React, {Component, PropTypes} from 'react';
import { NavFrame, NavFrameHeaderContainer, NavFrameBody, TabsPaneStatefulContainer, Tab }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { KBTab } from './Tabs/KBTab';
import { NewsTab } from './Tabs/NewsTab';
import { DownloadsTab } from './Tabs/DownloadsTab';
import { ToDoTab } from './Tabs/ToDoTab';

export class Nav extends Component {

  static propTypes = {
    isLoaded: PropTypes.bool.isRequired,
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
    const { articles, news, downloads, todo, setMine, isLoaded } = this.props;

    return (
      <NavFrame>
        <NavFrameHeaderContainer icon="icon-dp-streamline-edit-1">Publish</NavFrameHeaderContainer>
        <NavFrameBody isLoaded={isLoaded}>
          <TabsPaneStatefulContainer id="tab">
            <Tab title="KB">
              <KBTab articles={articles}
                     toggleGroupingVisibility={this.toggle}
                     closeGroupingVisibility={this.close}/>
            </Tab>
            <Tab title="News">
              <NewsTab news={news}
                       toggleGroupingVisibility={this.toggle}
                       closeGroupingVisibility={this.close}/>
            </Tab>
            <Tab icon="fa-download" title="Downloads">
              <DownloadsTab downloads={downloads}
                            toggleGroupingVisibility={this.toggle}
                            closeGroupingVisibility={this.close}/>
            </Tab>
            <Tab title="Todo">
              <ToDoTab todo={todo}
                       setMine={setMine}/>
            </Tab>
          </TabsPaneStatefulContainer>
        </NavFrameBody>
      </NavFrame>
    );
  }
}
