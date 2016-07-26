import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { MenuItem } from 'Component/Menu/MenuItem';

export class Menu extends React.Component {
  static propTypes = {
    section: PropTypes.object.isRequired
  };

  render() {
    let items = [];
    for (let item of this.props.section.items) {
      items.push(<MenuItem item={item} />)
    }
    let title = '';
    if (this.props.section.title)
      title = <h4>{this.props.section.title}</h4>;
    return <div className={classNames('item', {'with-divider': this.props.section.withDivider})}>
      {title}
      <div className="menu">
        {items}
      </div>
    </div>
  }
}