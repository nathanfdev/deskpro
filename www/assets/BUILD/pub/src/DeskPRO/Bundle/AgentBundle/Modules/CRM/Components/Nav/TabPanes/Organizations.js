import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { TabsPaneStatefulContainer, Tab, ListItem, LabelsDictionary }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { ListItemContainer } from '../ListItemContainer';

export class Organizations extends Component {

  static propTypes = {
    organizations: PropTypes.object.isRequired,
    labels:        PropTypes.object.isRequired
  };

  render() {
    const { labels, organizations } = this.props;
    const listOptions = { content: 'Organization', order_by: 'name', order_dir: constants.ORDER_ASC };

    return (
      <TabsPaneStatefulContainer id="orgTab">
        <Tab title="All">
          <ul>
            <ListItemContainer group="Organization" label="all" listOptions={listOptions}>
              <ListItem count={organizations.get('count')} label="All Organizations" />
            </ListItemContainer>
          </ul>
        </Tab>
        <Tab title="Labels">
          {labels && <LabelsDictionary labels={labels} onClick={() => {}} />}
        </Tab>
      </TabsPaneStatefulContainer>
    );
  }
}
