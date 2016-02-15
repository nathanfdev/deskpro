import React, {Component, PropTypes} from 'react';
import { toggleSelectedAction } from '../../../../../Application/Actions/massActions';
import { applyParams } from '../../../../Actions/crmListActions';
import { OrganizationCard } from './OrganizationCard';
import { PersonCard } from './PersonCard';
import { currentContentSelector, elementsSelector }
  from '../../../../Selectors/list';
import { collectionSelectorFactory, allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { selectedSelector} from '../../../../../Application/Selectors/massActions';
import { connect } from 'react-redux';

@connect(state => ({
  elements: elementsSelector(state),
  people: collectionSelectorFactory('Person', 'crm')(state),
  organizations: collectionSelectorFactory('Organization', 'crm')(state),
  selected: selectedSelector(state),
  usergroups: allSelectorFactory('UserGroup')(state),
  languages: collectionSelectorFactory('Language', 'crm')(state),
  content: currentContentSelector(state)
}))
export class CrmCardContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    elements: PropTypes.array,
    people: PropTypes.array,
    organizations: PropTypes.array,
    content: PropTypes.string.isRequired,
    usergroups: PropTypes.object.isRequired,
    languages: PropTypes.object.isRequired,
    selected: PropTypes.object.isRequired
  };

  viewEmployees(listOptions) {
    const { dispatch } = this.props;
    dispatch(applyParams(listOptions));
  }

  toggleSelected(id, e) {
    e.stopPropagation();
    const { dispatch } = this.props;
    dispatch(toggleSelectedAction(id));
  }

  render() {
    const { people, organizations, elements, selected, content, usergroups, languages } = this.props;
    if (content === 'organizations') {
      return (
        <div>
          {elements.map((id, index) =>
              <OrganizationCard key={index}
                                organization={organizations.get(id)}
                                selected={selected.indexOf(id) > -1}
                                toggleSelected={this.toggleSelected.bind(this, id)}
                                viewEmployees={this.viewEmployees.bind(this)}/>
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
                        organization={organizations.get(person.get('organization'))}
                        language={languages.get(person.get('language'))}
                        toggleSelected={this.toggleSelected.bind(this, id)}
                        selected={selected.indexOf(id) > -1}/>
          );
        })}
      </div>
    );
  }
}