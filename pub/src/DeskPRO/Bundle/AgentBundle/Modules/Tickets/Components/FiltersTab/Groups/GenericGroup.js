import React from "react";

export default class GenericGroup extends React.Component {
  render() {
    const {grouping, count, item} = this.props;
    
    return (
      <li>
        <div className="list-counter-bucket"><a href="#" className="list-counter">{count}</a></div>
        <a href="#" className="item">{grouping}: {item}</a>
      </li>
    );
  }
}
