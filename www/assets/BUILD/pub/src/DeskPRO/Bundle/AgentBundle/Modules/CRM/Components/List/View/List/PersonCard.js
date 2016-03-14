import React, {Component, PropTypes} from 'react';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import { Card, CardLine, CardLineLeft, CardLineRight, CardLineItem, CardCheckbox, CardDisc, CardLabel }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Card';

@injectIntl
export class PersonCard extends Component {

  static propTypes = {
    intl: intlShape.isRequired,
    person: PropTypes.object.isRequired,
    organization: PropTypes.object,
    usergroups: PropTypes.object.isRequired,
    language: PropTypes.object.isRequired,
    toggleSelected: PropTypes.func.isRequired,
    selected: PropTypes.bool
  };

  renderOrganization() {
    const { organization } = this.props;
    if (organization) {
      return (
        <CardLineItem>{organization.get('name')}</CardLineItem>
      );
    }
  }

  renderLabels() {
    const labels = this.props.person.get('labels');
    if (labels.size) {
      return (
        <CardLine>
          <CardLineItem>
            <i className="fa fa-tags"></i> {labels.map((label, index)=> <CardLabel key={index} label={label}/>)}
          </CardLineItem>
        </CardLine>
      );
    }
  }

  renderUserGroups() {
    const { usergroups, person } = this.props;
    const personGroups = person.get('usergroups').toJS();
    if (personGroups.size > 0) {
      return (
        personGroups.map((groupId, index) =>
          <CardLineItem key={index}>
            <i className="fa fa-group"></i>{usergroups.get(groupId).get('title')}
          </CardLineItem>)
      );
    }
  }

  renderLanguage() {
    const {language} = this.props;
    if (language) {
      return (
        <CardLineItem><CardDisc/>{language.get('title')} ({language.get('locale')})</CardLineItem>
      );
    }
  }

  render() {
    const { person, selected, toggleSelected } = this.props;

    return (
      <Card type="crm">

        <CardCheckbox selected={selected} onClick={toggleSelected}/>

        <CardLine>
          <CardLineLeft>
            <CardLineItem>[#{person.get('id')}] {person.get('name')}</CardLineItem>
            <CardLineItem><CardDisc/>{person.get('primary_email')}</CardLineItem>
          </CardLineLeft>
          <CardLineRight>
            {this.renderOrganization()}
            {person.get('organization_position') &&
            <CardLineItem><CardDisc/>{person.get('organization_position')}</CardLineItem>}
          </CardLineRight>
        </CardLine>

        {this.renderLabels()}

        <CardLine>
          <CardLineLeft>
            {this.renderUserGroups()}
          </CardLineLeft>
          <CardLineRight/>
        </CardLine>

        <CardLine>
          <CardLineLeft>
            <CardLineItem><FormattedRelative value={person.get('date_created')}/></CardLineItem>
            {this.renderLanguage()}
          </CardLineLeft>
          <CardLineRight>
            <CardLineItem icon="fa-envelope">{person.get('tickets_count')}</CardLineItem>
            <CardLineItem icon="fa-comment">{person.get('chats_count')}</CardLineItem>
          </CardLineRight>
        </CardLine>

      </Card>
    );
  }
}
