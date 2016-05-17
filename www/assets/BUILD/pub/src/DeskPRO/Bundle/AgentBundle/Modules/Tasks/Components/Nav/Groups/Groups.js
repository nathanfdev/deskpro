import React, { PropTypes } from 'react';
import { Section, SectionHeader, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { ListItemContainer } from '../ListItemContainer';

const options = {
  my:         {
    filter: { assigned_agent: ['me'] },
    label:  'My Tasks'
  },
  team:       {
    filter: { assigned_team: ['me'] },
    label:  'My Team Tasks'
  },
  department: {
    filter: { assigned_department: ['me'] },
    label:  'My Department Tasks'
  },
  delegated:  {
    filter: {
      not_assigned_agent: ['me'],
      creator:            'me'
    },
    label:  'My Delegated Tasks'
  },
  unassigned: {
    filter: { no_assignments: 1 },
    label:  'Unassigned Tasks'
  },
  all:        {
    filter: {},
    label:  'All Tasks'
  }
};

export class Groups extends React.Component {

  static propTypes = {
    options:        PropTypes.object.isRequired,
    groupsCountMap: PropTypes.object.isRequired
  };

  render() {
    const { groupsCountMap } = this.props;

    return (
      <Section>
        <SectionHeader>Tasks</SectionHeader>
        <ul>
          {Object.entries(options).map(kv => {
            const [type, params] = kv;
            return (
            <ListItemContainer key={type} urlHash={params.label} listOptions={params.filter}>
              <ListItem count={groupsCountMap[type]} label={params.label} />
            </ListItemContainer>
            );
          })}
        </ul>
      </Section>
    );
  }
}
