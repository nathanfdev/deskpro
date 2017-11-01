import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

export class LabelItem extends React.Component {

  static propTypes = {
    active:  PropTypes.bool,
    label:   PropTypes.object.isRequired,
    onClick: PropTypes.func
  };

  render() {
    const { label, active, onClick = () => {} } = this.props;
    const name = label.get('label');

    return (
      <a href="#" className={classNames('item-label', { active })} onClick={onClick}>
        {name}
      </a>
    );
  }
}
