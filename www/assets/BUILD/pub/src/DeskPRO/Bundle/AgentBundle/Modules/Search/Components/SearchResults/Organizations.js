import React from 'react';
import PropTypes from 'prop-types';

export default class Organizations extends React.Component {
  static propTypes = {
    organizations: PropTypes.array
  };

  static getLogo(org) {
    if (org.img) {
      return <img className="avatar" alt="avatar" src={org.img} />;
    }
    return null;
  }

  render() {
    const { organizations } = this.props;
    return (
      <section className="organizations">
        <header><h1>Organizations</h1></header>
        {organizations.map(org =>
          <div className="organization" key={org.id}>
            {Organizations.getLogo(org)}
            <span className="title">
              {org.name}
            </span>
            <span className="members">
              {`${org.members} Members`}
            </span>
          </div>
            )}
      </section>
    );
  }
}
