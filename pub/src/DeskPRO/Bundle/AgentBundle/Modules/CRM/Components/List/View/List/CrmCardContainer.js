import React, {Component, PropTypes} from 'react';
import { OrganizationCard } from './OrganizationCard';
import { PersonCard } from './PersonCard';
import { currentContentSelector, elementsSelector }
  from '../../../../Selectors/list';
import { peopleSelector, organizationsSelector, userGroupsSelector, languagesSelector }
  from '../../../../Selectors/recordStores';

import { connect } from 'react-redux';
@connect(state => {
  return ({
    elements: elementsSelector(state),
    people: peopleSelector(state),
    organizations: organizationsSelector(state),
    selected: state.CRM.list.get('selected'),
    usergroups: userGroupsSelector(state),
    languages: languagesSelector(state),
    content: currentContentSelector(state)
  });
})

export class CrmCardContainer extends Component {
  static propTypes = {
    elements: PropTypes.array,
    people: PropTypes.array,
    organizations: PropTypes.array,
    content: PropTypes.string.isRequired,
    usergroups: PropTypes.object.isRequired,
    languages: PropTypes.object.isRequired,
    selected: PropTypes.object.isRequired
  };

  render() {
    const { people, organizations, elements, selected, content, usergroups, languages } = this.props;
    if (content === 'organizations') {
      return (
        <div>
          {elements.map((id, index) =>
              <OrganizationCard key={index}
                                organization={organizations.get(id)}
                                selected={selected.includes(id)}/>
          )}
        </div>
      );
    }

    return (
      <div>
        {elements && elements.map((id, index) => {
          const person = people.get(id);
          return (
            <PersonCard key={index}
                        person={person}
                        usergroups={usergroups}
                        organization={organizations.get(person.organization)}
                        language={languages.get(person.language)}
                        selected={selected.includes('id')}/>
          );
        })}
      </div>
    );
  }
}