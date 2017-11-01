import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { NavFrame, NavFrameHeaderContainer, NavFrameBody, TabsPaneStatefulContainer, Tab }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { ContentTab } from './Tabs/ContentTab';
import { KBTab } from './Tabs/KBTab';
import { ToDoTab } from './Tabs/ToDoTab';

export class Nav extends Component {

  static propTypes = {
    isLoaded:  PropTypes.bool.isRequired,
    articles:  PropTypes.object.isRequired,
    news:      PropTypes.object.isRequired,
    downloads: PropTypes.object.isRequired,
    todo:      PropTypes.object.isRequired,
    setMine:   PropTypes.func.isRequired
  };

  render() {
    const { articles, news, downloads, todo, setMine, isLoaded } = this.props;

    return (
      <NavFrame>
        <NavFrameHeaderContainer icon="icon-dp-streamline-edit-1">Publish</NavFrameHeaderContainer>
        <NavFrameBody isLoaded={isLoaded}>
          <TabsPaneStatefulContainer id="tab">
            <Tab title="KB">
              <KBTab count={articles} />
            </Tab>
            <Tab title="News">
              <ContentTab content="news" count={news} />
            </Tab>
            <Tab icon="fa-download" title="Downloads">
              <ContentTab content="downloads" count={downloads} />
            </Tab>
            <Tab title="Todo">
              <ToDoTab todo={todo} setMine={setMine} />
            </Tab>
          </TabsPaneStatefulContainer>
        </NavFrameBody>
      </NavFrame>
    );
  }
}
