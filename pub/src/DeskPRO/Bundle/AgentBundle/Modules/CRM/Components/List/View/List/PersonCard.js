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
    const {labels} = this.props.person;
    if (labels.length) {
      return (
        <CardLineItem>
          <i className="fa fa-tags"></i> {labels.map((label, index)=> <CardLabel key={index} label={label}/>)}
        </CardLineItem>
      );
    }
  }

  renderUserGroups() {
    const {usergroups, person} = this.props;
    if (person.usergroups.length > 0) {
      return (
        person.usergroups.map((groupId, index) =>
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
    const { person, selected } = this.props;

    return (
      <Card type="crm">

        <CardCheckbox selected={selected} onClick={()=>{}}/>

        <CardLine>
          <CardLineLeft>
            <CardLineItem>[#{person.id}] {person.name}</CardLineItem>
            <CardLineItem><CardDisc/>{person.primary_email}</CardLineItem>
          </CardLineLeft>
          <CardLineRight>
            {this.renderOrganization()}
            {person.organization_position && <CardLineItem><CardDisc/>{person.organization_position}</CardLineItem>}
          </CardLineRight>
        </CardLine>

        <CardLine>
          <CardLineLeft>
            {this.renderLabels()}
            {this.renderUserGroups()}
          </CardLineLeft>
          <CardLineRight/>
        </CardLine>

        <CardLine>
          <CardLineLeft>
            <CardLineItem><FormattedRelative value={person.date_created}/></CardLineItem>
            {this.renderLanguage()}
          </CardLineLeft>
          <CardLineRight/>
        </CardLine>

      </Card>
    );
  }
}
