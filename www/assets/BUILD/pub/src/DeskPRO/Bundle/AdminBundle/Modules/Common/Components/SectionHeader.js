import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

class SectionHeader extends React.Component {

  static propTypes = {
    title:       PropTypes.string,
    description: PropTypes.string,
    dividing:    PropTypes.bool
  };

  render() {
    const { title, description, dividing } = this.props;

    return (
      <div className={classNames('section-header', { dividing })}>
        <h2>{title}</h2>
        {description && <div className="sub-header">{description}</div>}
      </div>
    );
  }
}

export default SectionHeader;
