import React from 'react';

export class ListItem extends React.Component {
  render() {
    const {count, label} = this.props;

    return (
      <li>
        {this.renderCount(count)}
        <a href="#" className="item">{label}</a>
      </li>
    );
  }

  renderCount(count) {
    if (!count) {
      return;
    }

    return (
      <div className="list-counter-bucket">
        <a className="list-counter active" href="#">{count}</a>
      </div>
    );
  }
}
