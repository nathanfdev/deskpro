import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import { Card, CardLine, CardLineLeft, CardLineRight, CardLineItem, CardCheckbox, CardDisc, CardLabel }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Card';

@injectIntl
export class PersonCard extends Component {

  static propTypes = {
    intl:           intlShape.isRequired,
    person:         PropTypes.object.isRequired,
    organization:   PropTypes.object,
    usergroups:     PropTypes.object.isRequired,
    language:       PropTypes.object.isRequired,
    toggleSelected: PropTypes.func.isRequired,
    selected:       PropTypes.bool,
    fields:         PropTypes.object.isRequired
  };

  handleClick = () => {
    const { person, toggleSelected } = this.props;
    toggleSelected(person.get('id'));
  };

  renderOrganization() {
    const { organization } = this.props;
    if (organization) {
      return (
        <CardLineItem>{organization.get('name')}</CardLineItem>
      );
    }
    return null;
  }

  renderLabels() {
    const labels = this.props.person.get('labels');
    if (labels.size) {
      return (
        <CardLine>
          <CardLineItem>
            <i className="fa fa-tags" /> {labels.map((label, index) => <CardLabel key={index} label={label} />)}
          </CardLineItem>
        </CardLine>
      );
    }
    return null;
  }

  renderUserGroups() {
    const { usergroups, person } = this.props;
    const personGroups = person.get('user_groups').toJS();
    if (personGroups.size > 0) {
      return (
        personGroups.map((groupId, index) =>
          <CardLineItem key={index}>
            <i className="fa fa-group" />{usergroups.get(groupId).get('title')}
          </CardLineItem>)
      );
    }
    return null;
  }

  renderLanguage() {
    const { language } = this.props;
    if (language) {
      return (
        <CardLineItem><CardDisc />{language.get('title')} ({language.get('locale')})</CardLineItem>
      );
    }
    return null;
  }

  renderField(field) {
    if (!field || !field.get('visible')) {
      return null;
    }

    const fieldId = field.get('id');
    const { person } = this.props;
    switch (fieldId) {

      case 'date_created':
        return (
          <CardLineItem key={fieldId}>
            <CardDisc />
            <FormattedRelative value={person.get(fieldId)} />
          </CardLineItem>
        );

      case 'language':
        if (!this.props.language) return null;
        return (
          <CardLineItem key={fieldId}>
            <CardDisc />
            {this.props.language.get('title')} ({this.props.language.get('locale')})
          </CardLineItem>
        );

      default:
        return null;
    }
  }

  render() {
    const { person, selected, fields } = this.props;

    return (
      <Card type="crm">

        <CardCheckbox selected={selected} onClick={this.handleClick} />

        <CardLine>
          <CardLineLeft>
            <CardLineItem>
              [#{person.get('id')}] {person.get('name')}
            </CardLineItem>
            <CardLineItem>
              <CardDisc />{person.get('primary_email')}
            </CardLineItem>
          </CardLineLeft>
          <CardLineRight>
            {this.renderOrganization()}
            {person.get('organization_position') &&
            <CardLineItem>
              <CardDisc />
              {person.get('organization_position')}
            </CardLineItem>}
          </CardLineRight>
        </CardLine>

        {this.renderLabels()}

        <CardLine>
          <CardLineLeft>
            {this.renderUserGroups()}
          </CardLineLeft>
          <CardLineRight />
        </CardLine>

        <CardLine>
          <CardLineLeft>
            {fields.map(field => this.renderField(field))}
          </CardLineLeft>
          <CardLineRight>
            <CardLineItem icon="fa-envelope">
              {person.get('tickets_count')}
            </CardLineItem>
            <CardLineItem icon="fa-comment">
              {person.get('chats_count')}
            </CardLineItem>
          </CardLineRight>
        </CardLine>

      </Card>
    );
  }
}
