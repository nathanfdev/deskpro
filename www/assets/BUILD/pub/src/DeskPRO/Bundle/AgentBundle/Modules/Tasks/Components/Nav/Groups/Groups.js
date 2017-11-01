import PropTypes from 'prop-types';
import React from 'react';
import { Section, SectionHeader, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { ListItemContainer } from '../ListItemContainer';

export class Groups extends React.Component {

  static propTypes = {
    groupsCountMap: PropTypes.object.isRequired,
    myTeams:        PropTypes.object
  };

  render() {
    const { groupsCountMap, myTeams } = this.props;
    const options = {
      my: {
        filter: { assigned_agent: ['me'] },
        label:  'My Tasks'
      }
    };

    if (myTeams.size > 0) {
      Object.assign(options, options, {
        team: {
          filter: { assigned_team: ['me'] },
          label:  'My Team Tasks'
        }
      });
    }

    Object.assign(options, options, {
      department: {
        filter: { assigned_department: ['me'] },
        label:  'My Department Tasks'
      },

      delegated: {
        filter: {
          not_assigned_agent: ['me'],
          creator:            'me'
        },

        label: 'My Delegated Tasks'
      },

      unassigned: {
        filter: { no_assignments: 1 },
        label:  'Unassigned Tasks'
      },

      all: {
        filter: {},
        label:  'All Tasks'
      }
    });

    return (
      <Section>
        <SectionHeader>Tasks</SectionHeader>
        <ul>
          {Object.entries(options).map(
            ([type, params]) =>
              <ListItemContainer
                key={type}
                urlHash={params.label}
                listOptions={params.filter}
              >
                <ListItem count={groupsCountMap[type]} label={params.label} />
              </ListItemContainer>
          )}
        </ul>
      </Section>
    );
  }
}
