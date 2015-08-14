import React from 'react';

export class ListItem extends React.Component {
  render() {
    return (
      <li>
        <div className="list-counter-bucket">
          <a className="list-counter active" href="#">{this.props.count}</a>
        </div>
        <a href="#" className="item">{this.props.label}</a>
      </li>
    );
  }
}
