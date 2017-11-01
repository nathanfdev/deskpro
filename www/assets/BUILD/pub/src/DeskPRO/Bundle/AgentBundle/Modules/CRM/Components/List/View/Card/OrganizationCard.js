import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { Card, CardLine, CardLineLeft, CardLineRight, CardLineItem, CardCheckbox, CardDisc, CardLabel }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Card';

@injectIntl

export class OrganizationCard extends Component {

  static propTypes = {
    intl:           intlShape.isRequired,
    viewEmployees:  PropTypes.func.isRequired,
    toggleSelected: PropTypes.func.isRequired,
    organization:   PropTypes.object.isRequired,
    selected:       PropTypes.bool,
    fields:         PropTypes.object.isRequired
  };

  handleClick = () => {
    const { organization, toggleSelected } = this.props;
    toggleSelected(organization.get('id'));
  };

  renderLabels = () => {
    const labels = this.props.organization.get('labels');
    if (labels.size) {
      return (
        <CardLineItem>
          <CardDisc />
          <i className="fa fa-tags" /> {labels.map((label, index) => <CardLabel key={index} label={label} />)}
          <CardDisc />
        </CardLineItem>
      );
    }
    return null;
  };

  renderDomains = () => {
    const emailDomains = this.props.organization.get('email_domains');
    if (emailDomains.size) {
      return (
        <CardLineItem>
          <CardDisc />
          {emailDomains.join(', ')}
        </CardLineItem>
      );
    }
    return null;
  };

  renderField(field) {
    if (!field || !field.get('visible')) {
      return null;
    }

    const fieldId = field.get('id');
    const { organization } = this.props;
    switch (fieldId) {

      case 'date_created':
        return (
          <CardLineItem>
            <CardDisc />,
            <FormattedRelative value={organization.get(fieldId)} />
          </CardLineItem>
        );

      default:
        return null;
    }
  }

  render() {
    const { organization, selected, viewEmployees, fields } = this.props;
    const clickParams = {
      content:      'people',
      orderBy:      'name',
      orderDir:     constants.ORDER_ASC,
      organization: organization.get('id')
    };

    return (
      <Card type="crm">

        <CardCheckbox selected={selected} onClick={this.handleClick} />

        <CardLine>
          <CardLineLeft>
            <CardLineItem>{organization.get('name')}</CardLineItem>
            {this.renderLabels()}
          </CardLineLeft>
          <CardLineRight>
            {this.renderDomains()}
          </CardLineRight>
        </CardLine>

        <CardLine>
          <CardLineLeft>
            <CardLineItem icon="fa-users" onClick={viewEmployees} clickParams={clickParams}>
              {organization.get('employees_count')}
            </CardLineItem>

            {this.renderField(fields.first())}

          </CardLineLeft>
          <CardLineRight>
            <CardLineItem icon="fa-envelope">
              {organization.get('tickets_count')}
            </CardLineItem>
            <CardLineItem icon="fa-comment">
              {organization.get('chats_count')}
            </CardLineItem>
          </CardLineRight>
        </CardLine>
      </Card>
    );
  }
}
