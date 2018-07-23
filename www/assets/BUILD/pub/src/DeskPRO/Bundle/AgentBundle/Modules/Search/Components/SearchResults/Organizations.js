import React from 'react';
import PropTypes from 'prop-types';
import { FormattedMessage } from 'react-intl';

export default class Organizations extends React.Component {
  static propTypes = {
    organizations: PropTypes.array,
    maxItems:      PropTypes.number,
  };

  static defaultProps = {
    maxItems: 1
  };

  static getLogo(org) {
    if (org.img) {
      return <img className="avatar" alt="avatar" src={org.img} />;
    }
    return null;
  }

  static renderOrganizations(organizations) {
    return organizations.map(org =>
      <div className="organization" key={org.id}>
        {Organizations.getLogo(org)}
        <span className="title">
          {org.name}
        </span>
        <span className="members">
          {`${org.members} Members`}
        </span>
      </div>
    );
  }

  constructor(props) {
    super(props);
    this.state = {
      expanded: false
    };
  }

  showMore = () => {
    this.setState({
      expanded: true
    });
  };

  renderCollapsed() {
    const { organizations, maxItems } = this.props;
    const collapsed = Organizations.renderOrganizations(organizations.slice(0, maxItems));
    collapsed.push(
      <div className="expand" key="expand" onClick={this.showMore}>
        <FormattedMessage id="agent.general.show_x_more" values={{ count: organizations.length - maxItems }} />
      </div>
    );
    return collapsed;
  }

  render() {
    const { organizations, maxItems } = this.props;
    return (
      <section className="organizations">
        <header><h1>Organizations</h1></header>
        { (organizations.length <= maxItems || this.state.expanded) ?
          Organizations.renderOrganizations(organizations)
          : this.renderCollapsed()
        }
      </section>
    );
  }
}
