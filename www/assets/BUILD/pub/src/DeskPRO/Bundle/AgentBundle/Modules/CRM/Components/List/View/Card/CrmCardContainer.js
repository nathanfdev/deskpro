import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { toggleSelectedAction } from '../../../../../Application/Actions/massActions';
import { applyParams } from '../../../../Actions/crmListActions';
import { OrganizationCard } from './OrganizationCard';
import { PersonCard } from './PersonCard';
import { currentContentSelector, elementsSelector } from '../../../../Selectors/list';
import { collectionSelectorFactory, allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { selectedSelector } from '../../../../../Application/Selectors/massActions';
import { connect } from 'react-redux';

@connect(state => ({
  elements:      elementsSelector(state),
  selected:      selectedSelector(state),
  content:       currentContentSelector(state),
  people:        collectionSelectorFactory('Person', 'crm')(state),
  organizations: collectionSelectorFactory('Organization', 'crm')(state),
  languages:     collectionSelectorFactory('Language', 'crm')(state),
  usergroups:    allSelectorFactory('UserGroup')(state)
}))

export class CrmCardContainer extends Component {
  static propTypes = {
    dispatch:      PropTypes.func.isRequired,
    elements:      PropTypes.object,
    people:        PropTypes.object,
    organizations: PropTypes.object,
    content:       PropTypes.string.isRequired,
    usergroups:    PropTypes.object.isRequired,
    languages:     PropTypes.object.isRequired,
    selected:      PropTypes.object.isRequired,
    peopleFields:  PropTypes.object.isRequired,
    orgFields:     PropTypes.object.isRequired
  };

  viewEmployees = (listOptions) => {
    const { dispatch } = this.props;
    dispatch(applyParams(listOptions));
  };

  toggleSelected = (id) => {
    const { dispatch } = this.props;
    dispatch(toggleSelectedAction(id));
  };

  renderOrganizationCard = (id, index) => {
    const { organizations, selected, orgFields } = this.props;

    return (
      <OrganizationCard
        key={index}
        organization={organizations.get(id)}
        selected={selected.indexOf(id) > -1}
        toggleSelected={this.toggleSelected}
        viewEmployees={this.viewEmployees}
        fields={orgFields}
      />
    );
  };

  renderPersonCard = (id, index) => {
    const { people, organizations, selected, usergroups, languages, peopleFields } = this.props;
    const person = people.get(id);

    return (
      <PersonCard
        key={index}
        person={person}
        usergroups={usergroups}
        organization={organizations.get(person.get('organization'))}
        language={languages.get(person.get('language'))}
        toggleSelected={this.toggleSelected}
        selected={selected.indexOf(id) > -1}
        fields={peopleFields}
      />
    );
  };

  render() {
    const { elements, content } = this.props;
    if (content === 'Organization') {
      return (
        <div>
          {elements.map((id, index) => this.renderOrganizationCard(id, index))}
        </div>
      );
    }

    return (
      <div>
        {elements && elements.map((id, index) => this.renderPersonCard(id, index))}
      </div>
    );
  }
}
