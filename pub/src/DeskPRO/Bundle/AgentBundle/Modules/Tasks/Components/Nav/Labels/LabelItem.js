import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class LabelItem extends React.Component {

  static propTypes = {
    active: PropTypes.bool,
    label: PropTypes.object.isRequired,
    onClick: PropTypes.func
  };

  render() {
    const { label, active, onClick = () => {} } = this.props;
    const name = label.get('label');
    const char = name && name.substr(0, 1).toUpperCase();

    return (
      <li>
        <span className="labelCharacter">{char}</span>
        <a href="#"
           className={classNames('item-label', {'active': active})}
           onClick={onClick}>

          {name}
        </a>
      </li>
    );
  }
}
