import React, {Component, PropTypes} from 'react';
import { NavFrame, NavFrameHeader, NavFrameBody, TabsPaneStatefulContainer, Tab, ListGroupingControl }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { KBTab } from './Tabs/KBTab';
import { NewsTab } from './Tabs/NewsTab';
import { DownloadsTab } from './Tabs/DownloadsTab';
import { ToDoTab } from './Tabs/ToDoTab';

export class Nav extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    loaded: PropTypes.bool.isRequired,
    articles: PropTypes.object.isRequired,
    news: PropTypes.object.isRequired,
    downloads: PropTypes.object.isRequired,
    todo: PropTypes.object.isRequired,
    grouping: PropTypes.object.isRequired,
    onGroupingChange: PropTypes.func.isRequired,
    toggleGroupingVisibility: PropTypes.func.isRequired,
    setMine: PropTypes.func.isRequired,
    dpWindow: PropTypes.object.isRequired
  };

  render() {
    const { articles, news, downloads, todo, grouping, onGroupingChange, toggleGroupingVisibility, setMine, dispatch, dpWindow, loaded } = this.props;
    const currentApp = dpWindow.get('activeAppId');


    return (
      <NavFrame dispatch={dispatch.bind(this)} dpWindow={dpWindow}>
        <div part="outer">

          <ListGroupingControl title="Articles"
                               options={grouping.get('options').toArray()}
                               visible={grouping.get('visibility').get('articles')}
                               onChange={onGroupingChange('articles')}
                               attachTo={this.refs.kb}
                               close={toggleGroupingVisibility('articles')}/>

          <ListGroupingControl title="News"
                               options={grouping.get('options').toArray()}
                               visible={grouping.get('visibility').get('news')}
                               onChange={onGroupingChange('news')}
                               attachTo={this.refs.news}
                               close={toggleGroupingVisibility('news')}/>

          <ListGroupingControl title="Downloads"
                               options={grouping.get('options').toArray()}
                               visible={grouping.get('visibility').get('downloads')}
                               onChange={onGroupingChange('downloads')}
                               attachTo={this.refs.downloads}
                               close={toggleGroupingVisibility('downloads')}/>

        </div>

        <div part="inner">
          <NavFrameHeader icon="icon-dp-streamline-edit-1" currentApp={currentApp}>
            Publish
          </NavFrameHeader>
          <NavFrameBody>
            <TabsPaneStatefulContainer id="tab">
              <Tab title="KB">
                <KBTab articles={articles}
                       loaded={loaded}
                       toggleGroupingVisibility={toggleGroupingVisibility}/>
              </Tab>
              <Tab title="News">
                <NewsTab news={news}
                         loaded={loaded}
                         toggleGroupingVisibility={toggleGroupingVisibility}/>
              </Tab>
              <Tab icon="fa-download">
                <DownloadsTab downloads={downloads}
                              loaded={loaded}
                              toggleGroupingVisibility={toggleGroupingVisibility}/>
              </Tab>
              <Tab title="Todo">
                <ToDoTab todo={todo}
                         loaded={loaded}
                         toggleGroupingVisibility={toggleGroupingVisibility}
                         setMine={setMine}/>
              </Tab>
            </TabsPaneStatefulContainer>
          </NavFrameBody>
        </div>
      </NavFrame>
    );
  }
}
