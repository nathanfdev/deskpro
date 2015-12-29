import React, {Component, PropTypes} from 'react';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import { Card, CardLine, CardLineLeft, CardLineRight, CardLineItem, CardCheckbox, CardDisc, CardLabel }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Card';

@injectIntl
export class OrganizationCard extends Component {

  static propTypes = {
    intl: intlShape.isRequired,
    organization: PropTypes.object.isRequired,
    selected: PropTypes.bool
  };

  renderLabels() {
    const {labels} = this.props.organization;
    if (labels.length) {
      return (
        <CardLineItem>
          <CardDisc/>
          <i className="fa fa-tags"></i> {labels.map((label, index)=> <CardLabel key={index} label={label}/>)}
          <CardDisc/>
        </CardLineItem>
      );
    }
  }

  renderDomains() {
    const {email_domains} = this.props.organization;
    if (email_domains.length) {
      return (
        <CardLineItem>
          <CardDisc/>
          {email_domains.join(', ')}
        </CardLineItem>
      );
    }
  }

  render() {
    const { organization, selected } = this.props;

    return (
      <Card type="crm">

        <CardCheckbox selected={selected} onClick={()=>{}}/>

        <CardLine>
          <CardLineLeft>
            <CardLineItem>{organization.name}</CardLineItem>
            {this.renderLabels()}
          </CardLineLeft>
          <CardLineRight>
            {this.renderDomains()}
          </CardLineRight>
        </CardLine>

        <CardLine>
          <CardLineLeft>
            <CardLineItem><FormattedRelative value={organization.date_created}/></CardLineItem>
          </CardLineLeft>
          <CardLineRight/>
        </CardLine>
      </Card>
    );
  }
}
