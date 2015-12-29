import React, {Component, PropTypes} from 'react';
import { OrganizationCard } from './OrganizationCard';
import { PersonCard } from './PersonCard';
import { currentContentSelector, peopleSelector, organizationsSelector }
  from '../../../../Selectors/list';
import { organizationsRecordsSelector, userGroupsSelector, languagesSelector } from '../../../../Selectors/recordStores';

import { connect } from 'react-redux';
@connect(state => {
  return ({
    people: peopleSelector(state),
    linkedOrganizations: organizationsRecordsSelector(state),
    organizations: organizationsSelector(state),
    selected: state.CRM.list.get('selected'),
    usergroups: userGroupsSelector(state),
    languages: languagesSelector(state),
    content: currentContentSelector(state)
  });
})

export class CrmCardContainer extends Component {
  static propTypes = {
    people: PropTypes.array,
    linkedOrganizations: PropTypes.object,
    organizations: PropTypes.array,
    content: PropTypes.string.isRequired,
    usergroups: PropTypes.object.isRequired,
    languages: PropTypes.object.isRequired,
    selected: PropTypes.object.isRequired
  };

  render() {
    const { people, linkedOrganizations, organizations, selected, content, usergroups, languages } = this.props;
    if (content === 'organizations') {
      return (
        <div>
          {organizations.map((element, index) =>
              <OrganizationCard key={index}
                                organization={element}
                                selected={selected.includes(element.id)}/>
          )}
        </div>
      );
    }

    return (
      <div>
        {people && people.map((element, index) =>
            <PersonCard key={index}
                        person={element}
                        organization={linkedOrganizations.get(element.organization)}
                        usergroups={usergroups}
                        language={languages.get(element.language)}
                        selected={selected.includes(element.id)}/>
        )}
      </div>
    );
  }
}