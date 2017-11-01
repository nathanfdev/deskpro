import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { TabsPaneStatefulContainer, Tab, LabelsDictionary }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { NestedList } from '../NestedList';

export class People extends Component {

  static propTypes = {
    users:  PropTypes.object.isRequired,
    labels: PropTypes.object.isRequired
  };

  render() {
    const { labels, users } = this.props;

    return (
      <TabsPaneStatefulContainer id="peopleTab">
        <Tab title="Groups">
          <NestedList items={[users.toJS()]} isAgent={0} group="people" alwaysExpanded />
        </Tab>
        <Tab title="Filters">Filters tab content</Tab>
        <Tab title="Labels">
          {labels && <LabelsDictionary labels={labels} onClick={() => {}} />}
        </Tab>
      </TabsPaneStatefulContainer>
    );
  }
}
